<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function index(Product $product)
    {
        $variants = $product->variants()->with('attributeValues.attribute')->paginate(10);
        return view('admin.product_variants.index', compact('product', 'variants'));
    }

    public function create(Product $product)
    {
        $attributes = Attribute::with('values')->get();
        return view('admin.product_variants.create', compact('product', 'attributes'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|max:255|unique:product_variants,sku',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attribute_values' => 'required|array',
            'attribute_values.*' => 'exists:attribute_values,id',
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

            $variant->attributeValues()->attach($request->attribute_values);

            DB::commit();
            return redirect()->route('admin.products.edit', $product->id)
                             ->with('success', 'Product variant created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($imagePath) { Storage::disk('public')->delete($imagePath); }
            \Log::error("Error creating product variant: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product variant. ' . $e->getMessage());
        }
    }

    public function show(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        $variant->load('attributeValues.attribute');
        return view('admin.product_variants.show', compact('product', 'variant'));
    }

    public function edit(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        $attributes = Attribute::with('values')->get();
        $currentAttributeValueIds = $variant->attributeValues->pluck('id')->toArray();

        return view('admin.product_variants.edit', compact('product', 'variant', 'attributes', 'currentAttributeValueIds'));
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
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
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = $request->file('image')->store('product_variants', 'public');
            } elseif ($request->input('remove_image')) {
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = null;
            }

            $variant->update([
                'sku' => $request->sku,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'image' => $imagePath,
            ]);

            $variant->attributeValues()->sync($request->attribute_values);

            DB::commit();
            return redirect()->route('admin.products.edit', $product->id)
                             ->with('success', 'Product variant updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->hasFile('image') && $imagePath && $imagePath !== $variant->getOriginal('image')) {
                Storage::disk('public')->delete($imagePath);
            }
            \Log::error("Error updating product variant: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product variant. ' . $e->getMessage());
        }
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
        if ($variant->image) { Storage::disk('public')->delete($variant->image); }
        $variant->delete();

        return redirect()->route('admin.products.edit', $product->id)
                         ->with('success', 'Product variant deleted successfully.');
    }

    public function getValuesByAttribute(Attribute $attribute)
    {
        return response()->json($attribute->values->pluck('value', 'id'));
    }
}