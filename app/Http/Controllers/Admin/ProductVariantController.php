<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For image handling
use Illuminate\Support\Facades\DB; // For transactions

class ProductVariantController extends Controller
{
    /**
     * Display a listing of product variants for a specific product.
     * (Accessible from product show/edit page)
     */
    public function index(Product $product)
    {
        $variants = $product->variants()->with('attributeValues.attribute')->paginate(10);
        return view('admin.product_variants.index', compact('product', 'variants'));
    }

    /**
     * Show the form for creating a new product variant for a specific product.
     */
    public function create(Product $product)
    {
        // Get all attributes and their values to build the variant selection
        $attributes = Attribute::with('values')->get();
        return view('admin.product_variants.create', compact('product', 'attributes'));
    }

    /**
     * Store a newly created product variant in storage.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|max:255|unique:product_variants,sku',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attribute_values' => 'required|array', // Array of selected attribute_value_ids
            'attribute_values.*' => 'exists:attribute_values,id', // Each ID must exist
        ]);

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product_variants', 'public');
            }

            $variant = $product->variants()->create([
                'sku' => $request->sku,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'image' => $imagePath,
            ]);

            // Attach attribute values to the variant
            $variant->attributeValues()->attach($request->attribute_values);

            DB::commit();
            return redirect()->route('admin.products.edit', $product->id)
                             ->with('success', 'Product variant created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($imagePath) { Storage::disk('public')->delete($imagePath); } // Clean up uploaded image
            \Log::error("Error creating product variant: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product variant. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product variant.
     */
    public function show(Product $product, ProductVariant $variant)
    {
        // Ensure the variant belongs to the correct product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        $variant->load('attributeValues.attribute');
        return view('admin.product_variants.show', compact('product', 'variant'));
    }

    /**
     * Show the form for editing the specified product variant.
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        // Ensure the variant belongs to the correct product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        $attributes = Attribute::with('values')->get();
        // Get current attribute values for the variant to pre-select them
        $currentAttributeValueIds = $variant->attributeValues->pluck('id')->toArray();

        return view('admin.product_variants.edit', compact('product', 'variant', 'attributes', 'currentAttributeValueIds'));
    }

    /**
     * Update the specified product variant in storage.
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        // Ensure the variant belongs to the correct product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }

        $request->validate([
            'sku' => 'nullable|string|max:255|unique:product_variants,sku,' . $variant->id,
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attribute_values' => 'required|array',
            'attribute_values.*' => 'exists:attribute_values,id',
        ]);

        DB::beginTransaction();
        try {
            $imagePath = $variant->image;
            if ($request->hasFile('image')) {
                if ($imagePath) { Storage::disk('public')->delete($imagePath); } // Delete old image
                $imagePath = $request->file('image')->store('product_variants', 'public');
            } elseif ($request->input('remove_image')) { // Handle explicit image removal
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = null;
            }

            $variant->update([
                'sku' => $request->sku,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'image' => $imagePath,
            ]);

            // Sync attribute values: detach old, attach new
            $variant->attributeValues()->sync($request->attribute_values);

            DB::commit();
            return redirect()->route('admin.products.edit', $product->id)
                             ->with('success', 'Product variant updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            // If new image uploaded and error occurs, clean it up
            if ($request->hasFile('image') && $imagePath && $imagePath !== $variant->getOriginal('image')) {
                Storage::disk('public')->delete($imagePath);
            }
            \Log::error("Error updating product variant: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product variant. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product variant from storage.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        // Ensure the variant belongs to the correct product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        if ($variant->image) { Storage::disk('public')->delete($variant->image); }
        $variant->delete();

        return redirect()->route('admin.products.edit', $product->id)
                         ->with('success', 'Product variant deleted successfully.');
    }

    /**
     * Helper to get attribute values for dynamic dropdown in forms.
     */
    public function getValuesByAttribute(Attribute $attribute)
    {
        return response()->json($attribute->values->pluck('value', 'id'));
    }
}