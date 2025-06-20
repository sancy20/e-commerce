<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'vendor')->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $mainCategories = Category::mainCategories()->with('children')->get();
        $attributes = Attribute::with('values')->get();

        return view('admin.products.create', compact('mainCategories', 'attributes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products')],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'nullable|boolean',
            'base_attribute_values' => 'nullable|array',
            'base_attribute_values.*' => 'nullable|array',
            'variants_data' => 'nullable|array',
            'variants_data.*.attribute_values' => 'required_with:variants_data|array',
            'variants_data.*.price' => 'required|numeric|min:0',
            'variants_data.*.stock_quantity' => 'required|integer|min:0',
            'variants_data.*.sku' => 'nullable|string|max:255',
            'variants_data.*.image' => 'nullable|image|max:2048',
            'variants_data.*.attribute_values' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $productData = $request->only('name', 'category_id', 'price', 'sku', 'stock_quantity', 'description');
            $productData['is_featured'] = $request->has('is_featured');
            
            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($productData);
            
            if ($request->has('variants_data') && !empty($request->variants_data)) {
                 foreach ($request->variants_data as $variantData) {
                    $variant = $product->variants()->create($variantData);
                    if (isset($variantData['image'])) {
                        $variant->image = $variantData['image']->store('products/variants', 'public');
                        $variant->save();
                    }
                    $attributeValueIds = collect($variantData['attribute_values'])->flatten()->filter()->all();
                    $variant->attributeValues()->sync($attributeValueIds);
                }
            } elseif ($request->has('base_attribute_values')) {
                $attributeValueIds = collect($request->base_attribute_values)->flatten()->filter()->all();
                $product->attributeValues()->sync($attributeValueIds);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin Product Creation Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error creating the product.')->withInput();
        }
    }

    public function edit(Product $product)
    {
        $mainCategories = Category::mainCategories()->with('children')->get();
        $attributes = Attribute::with('values')->get();
        
        return view('admin.products.edit', compact('product', 'mainCategories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id', 
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_base_image' => 'nullable|boolean',
        ]);

        $product->update([
            'name' => $validatedData['name'],
            'category_id' => $validatedData['category_id'],
            'price' => $validatedData['price'],
            'sku' => $validatedData['sku'],
            'stock_quantity' => $validatedData['stock_quantity'],
            'description' => $validatedData['description'],
            'is_featured' => $request->has('is_featured'),
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
            $product->save();
        } elseif ($request->has('remove_base_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
                $product->image = null;
                $product->save();
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product deleted successfully.');
    }
}