<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category'])->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }
    
    public function create()
    {
        $mainCategories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get();
        return view('admin.products.create', compact('mainCategories', 'attributes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'nullable|boolean',
            'variants_data' => 'nullable|array',
            'variants_data.*.price' => 'required_with:variants_data|numeric|min:0',
            'variants_data.*.stock_quantity' => 'required_with:variants_data|integer|min:0',
            'variants_data.*.sku' => 'nullable|string|max:100|unique:product_variants,sku',
            'variants_data.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variants_data.*.attribute_values' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $productData = [
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
                'category_id' => $validatedData['category_id'],
                'price' => $validatedData['price'],
                'sku' => $validatedData['sku'],
                'stock_quantity' => $validatedData['stock_quantity'],
                'description' => $validatedData['description'],
                'is_featured' => $request->has('is_featured'),
            ];

            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($productData);

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            if ($request->has('variants_data')) {
                foreach ($request->variants_data as $variantData) {
                    $newVariant = $product->variants()->create([
                        'price' => $variantData['price'],
                        'sku' => $variantData['sku'],
                        'stock_quantity' => $variantData['stock_quantity'],
                    ]);
            
                    if (isset($variantData['image'])) {
                        $newVariant->image = $variantData['image']->store('products/variants', 'public');
                        $newVariant->save();
                    }
            
                    if (isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])) {
                        $valueIdsToSync = [];
                        foreach ($variantData['attribute_values'] as $values) {
                            $valueIdsToSync = array_merge($valueIdsToSync, (array)$values);
                        }
                        $newVariant->attributeValues()->sync($valueIdsToSync);
                    }
                }
            }            

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product and variants created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create product. Error: ' . $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $mainCategories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = Attribute::with('values')->get();
        $product->load('variants.attributeValues');
        
        return view('admin.products.edit', compact('product', 'mainCategories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'image' => 'nullable|image|max:2048',
            'is_featured' => 'nullable|boolean',
            'delete_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $productData = $request->only('name', 'category_id', 'price', 'sku', 'stock_quantity', 'description');
            $productData['is_featured'] = $request->has('is_featured');
            
            if ($request->has('remove_base_image')) {
                if($product->image) Storage::disk('public')->delete($product->image);
                $productData['image'] = null;
            }
            if ($request->hasFile('image')) {
                if($product->image) Storage::disk('public')->delete($product->image);
                $productData['image'] = $request->file('image')->store('products', 'public');
            }
            
            $product->update($productData);

            if ($request->has('delete_images')) {
                $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)->where('product_id', $product->id)->get();
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            if ($request->has('variants_to_delete')) {
                $variantsToDelete = $product->variants()->whereIn('id', $request->variants_to_delete)->get();
                foreach($variantsToDelete as $variant) {
                    if ($variant->image) Storage::disk('public')->delete($variant->image);
                    $variant->delete();
                }
            }

            if ($request->has('existing_variants')) {
                foreach ($request->existing_variants as $id => $variantData) {
                    if ($variant = $product->variants()->find($id)) {
                        $variant->update([
                            'price' => $variantData['price'],
                            'sku' => $variantData['sku'],
                            'stock_quantity' => $variantData['stock_quantity'],
                        ]);
                        if (isset($variantData['remove_image'])) {
                            if($variant->image) Storage::disk('public')->delete($variant->image);
                            $variant->image = null;
                        }
                        if (isset($variantData['image'])) {
                            if($variant->image) Storage::disk('public')->delete($variant->image);
                            $variant->image = $variantData['image']->store('products/variants', 'public');
                        }
                        $variant->save();
                        $attributeValueIds = collect($variantData['attribute_values'])->flatten()->filter()->all();
                        $variant->attributeValues()->sync($attributeValueIds);
                    }
                }
            }

            if ($request->has('new_variants_data')) {
                 foreach ($request->new_variants_data as $variantData) {
                    $variant = $product->variants()->create([
                        'price' => $variantData['price'],
                        'sku' => $variantData['sku'],
                        'stock_quantity' => $variantData['stock_quantity'],
                    ]);
                    if (isset($variantData['image'])) {
                        $variant->image = $variantData['image']->store('products/variants', 'public');
                        $variant->save();
                    }
                    $attributeValueIds = collect($variantData['attribute_values'])->flatten()->filter()->all();
                    $variant->attributeValues()->sync($attributeValueIds);
                }
            }

            if ($request->has('delete_images')) {
                $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)->where('product_id', $product->id)->get();
                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            DB::commit();
        return redirect()->route('admin.products.edit', $product->id)->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update product. Error: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);

        foreach($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        
        foreach($product->variants as $variant) {
            if ($variant->image) Storage::disk('public')->delete($variant->image);
        }
        
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}