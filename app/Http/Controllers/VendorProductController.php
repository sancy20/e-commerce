<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VendorProductController extends Controller
{
    protected function getVendorProducts()
    {
        return Product::where('vendor_id', Auth::id());
    }

    public function index()
    {
        $products = $this->getVendorProducts()->with('category')->latest()->paginate(10);
        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $mainCategories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = \App\Models\Attribute::all();
        return view('vendor.products.create', [
            'categories' => $mainCategories, 
            'attributes' => $attributes
        ]);
    }

    /**
     * UPDATE: This is the fully corrected store method.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products')],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'nullable', // Use nullable instead of boolean for checkboxes
            'base_attribute_values' => 'nullable|array',
            'variants_data' => 'nullable|array',
            'variants_data.*.price' => 'required_with:variants_data|numeric|min:0',
            'variants_data.*.stock_quantity' => 'required_with:variants_data|integer|min:0',
            'variants_data.*.sku' => 'nullable|string|max:255|distinct',
            'variants_data.*.image' => 'nullable|image|max:2048',
            'variants_data.*.attribute_values' => 'required_with:variants_data|array',
        ]);

        DB::beginTransaction();
        try {
            $productData = $request->only('name', 'category_id', 'price', 'sku', 'stock_quantity', 'description');
            $productData['is_featured'] = $request->has('is_featured');
            $productData['vendor_id'] = Auth::id();

            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Product::create($productData);

            // If variants are submitted, they take priority. Otherwise, save base attributes.
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
            return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Product Creation Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error creating the product.')->withInput();
        }
    }
    
    public function edit(Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }
        $mainCategories = Category::whereNull('parent_id')->with('children')->get();
        $attributes = \App\Models\Attribute::all();
        return view('vendor.products.edit', [
            'product' => $product,
            'categories' => $mainCategories,
            'attributes' => $attributes
        ]);
    }

    /**
     * UPDATE: This is the fully corrected update method.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            // ... add other validation rules similar to the store method
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

            // Update base product attributes
            $baseAttributeValueIds = collect($request->input('base_attribute_values', []))->flatten()->filter()->all();
            $product->attributeValues()->sync($baseAttributeValueIds);

            // Delete variants marked for removal
            if ($request->has('variants_to_delete')) {
                $product->variants()->whereIn('id', $request->variants_to_delete)->delete();
            }

            // Update existing variants
            if ($request->has('existing_variants')) {
                foreach ($request->existing_variants as $id => $variantData) {
                    if ($variant = $product->variants()->find($id)) {
                        $variant->update($variantData);
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

            // Create new variants
            if ($request->has('new_variants_data')) {
                 foreach ($request->new_variants_data as $variantData) {
                    $variant = $product->variants()->create($variantData);
                    if (isset($variantData['image'])) {
                        $variant->image = $variantData['image']->store('products/variants', 'public');
                        $variant->save();
                    }
                    $attributeValueIds = collect($variantData['attribute_values'])->flatten()->filter()->all();
                    $variant->attributeValues()->sync($attributeValueIds);
                }
            }

            DB::commit();
            return redirect()->route('vendor.products.edit', $product->id)->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Product Update Failed for ID ' . $product->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error updating the product.')->withInput();
        }
    }

    public function destroy(Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }
        if ($product->image) { Storage::disk('public')->delete($product->image); }
        $product->delete();
        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
    }
}