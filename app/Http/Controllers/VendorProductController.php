<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Ensure Log facade is imported
use Illuminate\Validation\Rule; // Import Rule for advanced validation if needed

class VendorProductController extends Controller
{
    protected function getVendorProducts()
    {
        return Product::where('vendor_id', Auth::id());
    }

    public function index()
    {
        $products = $this->getVendorProducts()->with('category')->orderBy('name')->paginate(10);
        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::with('values')->get(); // Fetch attributes for variant form
        return view('vendor.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        Log::info('VendorProductController@store initiated.');
        Log::info('Request data (store): ' . json_encode($request->all()));

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock_quantity' => 'required|integer|min:0',
            // 'is_featured' => 'boolean',

            'variants_data' => 'nullable|array',
            'variants_data.*.attribute_values' => 'required_with:variants_data|array',
            'variants_data.*.attribute_values.*' => 'exists:attribute_values,id',
            'variants_data.*.sku' => 'nullable|string|max:255', // REMOVED UNIQUE FOR TESTING
            'variants_data.*.price' => 'nullable|numeric|min:0',
            'variants_data.*.stock_quantity' => 'required_with:variants_data|integer|min:0',
            'variants_data.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        Log::info('Validation passed in store method.');

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'vendor_id' => Auth::id(),
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => \Illuminate\Support\Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'sku' => $request->sku,
                'image' => $imagePath,
                'stock_quantity' => $request->stock_quantity,
                'is_featured' => $request->has('is_featured'),
            ]);
            Log::info('Base product created with ID: ' . $product->id);


            // Handle Variants Creation
            if ($request->has('variants_data') && is_array($request->variants_data)) {
                Log::info('Processing ' . count($request->variants_data) . ' new variants for store method...');
                foreach ($request->variants_data as $index => $variantData) {
                    $variantImagePath = null;
                    if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantImagePath = $variantData['image']->store('product_variants', 'public');
                    }

                    Log::info("Attempting to create variant " . $index . " with data: " . json_encode($variantData));
                    try { // Inner try-catch for granular error catching
                        $variant = $product->variants()->create([
                            'sku' => $variantData['sku'] ?? null,
                            'price' => $variantData['price'] ?? null,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'image' => $variantImagePath,
                        ]);
                        Log::info('Variant ' . $index . ' created with ID: ' . $variant->id);

                        if (isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])) {
                            try { // <--- Inner try-catch for ATTACH
                                // Explicitly cast attribute values to integers
                                $attributeValueIds = collect($variantData['attribute_values'])->map(fn($id) => (int)$id)->toArray(); // <--- CRITICAL CHANGE
                                $attachResult = $variant->attributeValues()->attach($attributeValueIds);
                                Log::info('Variant ' . $index . ' attribute values attached: ' . json_encode($attributeValueIds) . ' Result: ' . json_encode($attachResult));
                            } catch (\Exception $attachE) {
                                Log::error('ATTENTION: Attach error for variant ' . $index . ' in store: ' . $attachE->getMessage() . ' Data: ' . json_encode($variantData['attribute_values']));
                                throw $attachE; // Re-throw to cause outer catch and rollback
                            }
                        } else {
                            Log::info('Variant ' . $index . ' has no attribute values to attach.');
                        }
                    } catch (\Exception $innerE) {
                        Log::error('Inner variant creation error for index ' . $index . ' in store: ' . $innerE->getMessage() . ' in ' . $innerE->getFile() . ' on line ' . $innerE->getLine());
                        throw $innerE; // Re-throw to trigger outer catch and rollback
                    }
                }
            } else {
                Log::info('No variants data found in request for store method.');
            }

            DB::commit();
            Log::info('DB commit successful for store method. Redirecting to product index.');
            return redirect()->route('vendor.products.index')
                             ->with('success', 'Product and its variants created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Outer transaction error in store method: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            if (isset($imagePath) && $imagePath) { Storage::disk('public')->delete($imagePath); } // Clean up base product image
            return redirect()->back()->with('error', 'Failed to create product. An internal error occurred: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }
        $product->load('variants.attributeValues.attribute');
        return view('vendor.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::with('values')->get();
        $product->load('variants.attributeValues');

        return view('vendor.products.edit', compact('product', 'categories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        Log::info('VendorProductController@update initiated for product ID: ' . $product->id);
        Log::info('Request data (update): ' . json_encode($request->all()));


        if ($product->vendor_id !== Auth::id()) {
            Log::warning('Unauthorized update attempt for product ID: ' . $product->id . ' by user ID: ' . Auth::id());
            abort(403);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stock_quantity' => 'required|integer|min:0',

            'existing_variants' => 'nullable|array',
            'existing_variants.*.id' => 'required_with:existing_variants|exists:product_variants,id',
            'existing_variants.*.attribute_values' => 'required_with:existing_variants|array',
            'existing_variants.*.attribute_values.*' => 'exists:attribute_values,id',
            'existing_variants.*.sku' => 'nullable|string|max:255', // REMOVED UNIQUE FOR TESTING
            'existing_variants.*.price' => 'nullable|numeric|min:0',
            'existing_variants.*.stock_quantity' => 'required_with:existing_variants|integer|min:0',
            'existing_variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_variants.*.remove_image' => 'nullable|boolean',

            // New variants on update page are sent under 'variants_data' from frontend
            'variants_data' => 'nullable|array', // Changed from new_variants_data to variants_data for frontend input
            'variants_data.*.attribute_values' => 'required_with:variants_data|array',
            'variants_data.*.attribute_values.*' => 'exists:attribute_values,id',
            'variants_data.*.sku' => 'nullable|string|max:255', // REMOVED UNIQUE FOR TESTING
            'variants_data.*.price' => 'nullable|numeric|min:0',
            'variants_data.*.stock_quantity' => 'required_with:variants_data|integer|min:0',
            'variants_data.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'variants_to_delete' => 'nullable|array',
            'variants_to_delete.*' => 'exists:product_variants,id',
        ]);
        Log::info('Validation passed in update method.');


        DB::beginTransaction();
        try {
            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = $request->file('image')->store('products', 'public');
            } elseif ($request->input('remove_base_image')) {
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = null;
            }

            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => \Illuminate\Support\Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'sku' => $request->sku,
                'image' => $imagePath,
                'stock_quantity' => $request->stock_quantity,
                'is_featured' => $request->has('is_featured'),
            ]);
            Log::info('Base product updated. Product ID: ' . $product->id);


            // --- Handle Existing Variants Update/Delete ---
            $variantsToKeepIds = [];
            if ($request->has('existing_variants') && is_array($request->existing_variants)) {
                Log::info('Processing ' . count($request->existing_variants) . ' existing variants...');
                foreach ($request->existing_variants as $index => $variantData) {
                    $variant = ProductVariant::find($variantData['id']);
                    if (!$variant || $variant->product_id !== $product->id) {
                        Log::warning('Skipping existing variant ' . ($variantData['id'] ?? 'N/A') . ' - not found or not owned by product. Data: ' . json_encode($variantData));
                        continue;
                    }
                    Log::info("Attempting to update existing variant " . $variant->id . " with data: " . json_encode($variantData));

                    $currentVariantImage = $variant->image;
                    if (isset($variantData['image']) && $variantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        if ($currentVariantImage) { Storage::disk('public')->delete($currentVariantImage); }
                        $currentVariantImage = $variantData['image']->store('product_variants', 'public');
                    } elseif (isset($variantData['remove_image']) && $variantData['remove_image']) {
                        if ($currentVariantImage) { Storage::disk('public')->delete($currentVariantImage); }
                        $currentVariantImage = null;
                    }

                    try { // Inner try-catch for existing variant update
                        $variant->update([
                            'sku' => $variantData['sku'] ?? null,
                            'price' => $variantData['price'] ?? null,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'image' => $currentVariantImage,
                        ]);
                        Log::info('Existing variant ' . $variant->id . ' updated successfully.');

                        if (isset($variantData['attribute_values']) && is_array($variantData['attribute_values'])) {
                            try { // <--- Inner try-catch for SYNC
                                // Explicitly cast attribute values to integers
                                $attributeValueIds = collect($variantData['attribute_values'])->map(fn($id) => (int)$id)->toArray(); // <--- CRITICAL CHANGE
                                $syncResult = $variant->attributeValues()->sync($attributeValueIds);
                                Log::info('Existing variant ' . $variant->id . ' attribute values synced: ' . json_encode($attributeValueIds) . ' Result: ' . json_encode($syncResult));
                            } catch (\Exception $syncE) {
                                Log::error('ATTENTION: Sync error for existing variant ' . $variant->id . ': ' . $syncE->getMessage() . ' Data: ' . json_encode($variantData['attribute_values']));
                                throw $syncE;
                            }
                        } else {
                            Log::info('Existing variant ' . $variant->id . ' has no attribute values to sync.');
                        }
                    } catch (\Exception $innerE) {
                        Log::error('Inner existing variant update error for ID ' . $variant->id . ': ' . $innerE->getMessage() . ' in ' . $innerE->getFile() . ' on line ' . $innerE->getLine());
                        throw $innerE;
                    }
                    $variantsToKeepIds[] = $variant->id;
                }
                Log::info('Finished processing existing variants.');
            } else {
                Log::info('No existing variants data found in request for update method.');
            }

            // Delete variants that were not in the 'existing_variants' list
            if ($request->has('variants_to_delete') && is_array($request->variants_to_delete)) {
                Log::info('Processing deletion of ' . count($request->variants_to_delete) . ' variants via checkbox.');
                foreach ($request->variants_to_delete as $variantIdToDelete) {
                    $variantToDelete = ProductVariant::find($variantIdToDelete);
                    if ($variantToDelete && $variantToDelete->product_id === $product->id) {
                        Log::info('Deleting variant ID: ' . $variantToDelete->id . ' via checkbox.');
                        if ($variantToDelete->image) { Storage::disk('public')->delete($variantToDelete->image); }
                        $variantToDelete->delete();
                    } else {
                        Log::warning('Attempted to delete non-existent or unowned variant ID: ' . $variantIdToDelete);
                    }
                }
            } else {
                 // Fallback for variants removed from UI without explicit delete checkbox
                 // This ensures any variant previously existing but NOT submitted in existing_variants
                 // AND NOT explicitly marked for delete via checkbox is also deleted.
                 $product->variants()->whereNotIn('id', $variantsToKeepIds)->each(function($variant) {
                     Log::info('Deleting variant ID: ' . $variant->id . ' (removed from UI, not in variantsToKeepIds).');
                     if ($variant->image) { Storage::disk('public')->delete($variant->image); }
                     $variant->delete();
                 });
            }
            Log::info('Deletion logic for old variants executed.');


            // --- Handle New Variants Creation ---
            // The frontend sends new variants under 'variants_data' even on edit page
            if ($request->has('variants_data') && is_array($request->variants_data)) { // <-- CHANGED THIS LINE
                Log::info('Processing ' . count($request->variants_data) . ' new variants for update method...');
                foreach ($request->variants_data as $index => $newVariantData) {
                    $newVariantImagePath = null;
                    if (isset($newVariantData['image']) && $newVariantData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        $newVariantImagePath = $newVariantData['image']->store('product_variants', 'public');
                    }
                    Log::info("Attempting to create new variant " . $index . " with data: " . json_encode($newVariantData));

                    try { // Inner try-catch for new variant creation
                        $newVariant = $product->variants()->create([
                            'sku' => $newVariantData['sku'] ?? null,
                            'price' => $newVariantData['price'] ?? null,
                            'stock_quantity' => $newVariantData['stock_quantity'] ?? 0,
                            'image' => $newVariantImagePath,
                        ]);
                        Log::info('New variant ' . $index . ' created with ID: ' . $newVariant->id);

                        if (isset($newVariantData['attribute_values']) && is_array($newVariantData['attribute_values'])) {
                            try { // <--- Inner try-catch for ATTACH
                                // Explicitly cast attribute values to integers
                                $attributeValueIds = collect($newVariantData['attribute_values'])->map(fn($id) => (int)$id)->toArray(); // <--- CRITICAL CHANGE
                                $attachResult = $newVariant->attributeValues()->attach($attributeValueIds);
                                Log::info('New variant ' . $index . ' attribute values attached: ' . json_encode($attributeValueIds) . ' Result: ' . json_encode($attachResult));
                            } catch (\Exception $attachE) {
                                Log::error('ATTENTION: Attach error for new variant ' . $index . ': ' . $attachE->getMessage() . ' Data: ' . json_encode($newVariantData['attribute_values']));
                                throw $attachE;
                            }
                        } else {
                            Log::info('New variant ' . $index . ' has no attribute values to attach.');
                        }
                    } catch (\Exception $innerE) {
                        Log::error('Inner new variant creation error for index ' . $index . ': ' . $innerE->getMessage() . ' in ' . $innerE->getFile() . ' on line ' . $innerE->getLine());
                        throw $innerE;
                    }
                }
                Log::info('Finished processing new variants.');
            } else {
                Log::info('No new variants data found in request for update method (checked variants_data).');
            }


            DB::commit();
            Log::info('DB commit successful for update method. Redirecting to vendor product index.');
            return redirect()->route('vendor.products.index')
                             ->with('success', 'Product and its variants updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Outer transaction error in update method: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            if ($request->hasFile('image') && isset($imagePath) && $imagePath && $imagePath !== $product->getOriginal('image')) {
                Storage::disk('public')->delete($imagePath);
            }
            return redirect()->back()->with('error', 'Failed to update product. An internal error occurred: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        if ($product->vendor_id !== Auth::id()) { abort(403); }
        if ($product->image) { Storage::disk('public')->delete($product->image); }
        $product->delete();

        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product deleted successfully.');
    }
}