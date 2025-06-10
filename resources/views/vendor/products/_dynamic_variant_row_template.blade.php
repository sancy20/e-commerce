<div class="variant-row border border-gray-300 rounded-lg p-4 mb-4 relative" data-variant-index="JS_INDEX_PLACEHOLDER" data-is-new="true">
    <button type="button" class="remove-variant-btn absolute top-2 right-2 bg-red-500 hover:bg-red-700 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold">&times;</button>
    <h3 class="text-lg font-semibold text-gray-700 mb-3">Variant #JS_DISPLAY_INDEX_PLACEHOLDER</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="variants_data_JS_INDEX_PLACEHOLDER_sku" class="block text-gray-700 text-sm font-bold mb-2">SKU (Optional):</label>
            <input type="text" name="variants_data[JS_INDEX_PLACEHOLDER][sku]" id="variants_data_JS_INDEX_PLACEHOLDER_sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="JS_SKU_PLACEHOLDER">
            <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="variants_data.JS_INDEX_PLACEHOLDER.sku"></p>
        </div>
        <div>
            <label for="variants_data_JS_INDEX_PLACEHOLDER_price" class="block text-gray-700 text-sm font-bold mb-2">Price (Optional):</label>
            <input type="number" name="variants_data[JS_INDEX_PLACEHOLDER][price]" id="variants_data_JS_INDEX_PLACEHOLDER_price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="JS_PRICE_PLACEHOLDER">
            <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="variants_data.JS_INDEX_PLACEHOLDER.price"></p>
        </div>
        <div>
            <label for="variants_data_JS_INDEX_PLACEHOLDER_stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
            <input type="number" name="variants_data[JS_INDEX_PLACEHOLDER][stock_quantity]" id="variants_data_JS_INDEX_PLACEHOLDER_stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="JS_STOCK_QUANTITY_PLACEHOLDER" required>
            <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="variants_data.JS_INDEX_PLACEHOLDER.stock_quantity"></p>
        </div>
        <div>
            <label for="variants_data_JS_INDEX_PLACEHOLDER_image" class="block text-gray-700 text-sm font-bold mb-2">Image (Optional):</label>
            <input type="file" name="variants_data[JS_INDEX_PLACEHOLDER][image]" id="variants_data_JS_INDEX_PLACEHOLDER_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="variants_data.JS_INDEX_PLACEHOLDER.image"></p>
            <p class="text-gray-600 text-xs mt-1 js-image-preview">JS_IMAGE_PREVIEW_PLACEHOLDER</p>
        </div>
    </div>

    <h4 class="text-md font-semibold text-gray-700 mt-4 mb-2">Attributes:</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($attributes as $attribute)
            <div>
                <label for="variants_data_JS_INDEX_PLACEHOLDER_attribute_{{ $attribute->id }}" class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                <select name="variants_data[JS_INDEX_PLACEHOLDER][attribute_values][]" id="variants_data_JS_INDEX_PLACEHOLDER_attribute_{{ $attribute->id }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    <option value="">-- Select {{ $attribute->name }} --</option>
                    @foreach ($attribute->values as $value)
                        <option value="{{ $value->id }}" JS_SELECTED_ATTRIBUTE_VALUE_{{ $attribute->id }}_{{ $value->id }} >{{ $value->value }}</option>
                    @endforeach
                </select>
                <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="variants_data.JS_INDEX_PLACEHOLDER.attribute_values"></p>
            </div>
        @endforeach
    </div>
</div>