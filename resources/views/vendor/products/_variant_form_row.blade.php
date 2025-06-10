<div class="variant-row border border-gray-300 rounded-lg p-4 mb-4 relative" data-variant-index="{{ $index }}" data-is-new="{{ $is_new ? 'true' : 'false' }}">
    <button type="button" class="remove-variant-btn absolute top-2 right-2 bg-red-500 hover:bg-red-700 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold">&times;</button>
    <h3 class="text-lg font-semibold text-gray-700 mb-3">Variant #{{ $displayIndex }}</h3> {{-- Use PHP variable here --}}
    @unless($is_new)
        <input type="hidden" name="{{ $namePrefix }}[id]" value="{{ $variant->id }}">
    @endunless
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="{{ $namePrefix }}_sku" class="block text-gray-700 text-sm font-bold mb-2">SKU (Optional):</label>
            <input type="text" name="{{ $namePrefix }}[sku]" id="{{ $namePrefix }}_sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error($namePrefix . '.sku') border-red-500 @enderror" value="{{ old($namePrefix . '.sku', $variant->sku ?? '') }}">
            @error($namePrefix . '.sku')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $namePrefix }}_price" class="block text-gray-700 text-sm font-bold mb-2">Price (Optional):</label>
            <input type="number" name="{{ $namePrefix }}[price]" id="{{ $namePrefix }}_price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error($namePrefix . '.price') border-red-500 @enderror" value="{{ old($namePrefix . '.price', $variant->price ?? '') }}">
            @error($namePrefix . '.price')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $namePrefix }}_stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
            <input type="number" name="{{ $namePrefix }}[stock_quantity]" id="{{ $namePrefix }}_stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error($namePrefix . '.stock_quantity') border-red-500 @enderror" value="{{ old($namePrefix . '.stock_quantity', $variant->stock_quantity ?? '0') }}" required>
            @error($namePrefix . '.stock_quantity')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $namePrefix }}_image" class="block text-gray-700 text-sm font-bold mb-2">Image (Optional):</label>
            <input type="file" name="{{ $namePrefix }}[image]" id="{{ $namePrefix }}_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error($namePrefix . '.image') border-red-500 @enderror">
            @error($namePrefix . '.image')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
            @if (!$is_new && $variant->image)
                <p class="text-gray-600 text-xs mt-1">Current: <img src="{{ asset('storage') }}/{{ $variant->image }}" class="h-10 w-10 object-cover inline-block ml-2"></p>
                <label class="block mt-1 text-gray-700 text-sm">
                    <input type="checkbox" name="{{ $namePrefix }}[remove_image]" value="1" class="mr-1" {{ old($namePrefix . '.remove_image') ? 'checked' : '' }}> Remove current image
                </label>
            @endif
        </div>
    </div>

    <h4 class="text-md font-semibold text-gray-700 mt-4 mb-2">Attributes:</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($attributes as $attribute)
            <div>
                <label for="{{ $namePrefix }}_attribute_{{ $attribute->id }}" class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                <select name="{{ $namePrefix }}[attribute_values][]" id="{{ $namePrefix }}_attribute_{{ $attribute->id }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error($namePrefix . '.attribute_values') border-red-500 @enderror" required>
                    <option value="">-- Select {{ $attribute->name }} --</option>
                    @foreach ($attribute->values as $value)
                        <option value="{{ $value->id }}" {{ in_array($value->id, $selectedAttributeValueIds ?? []) ? 'selected' : '' }}>{{ $value->value }}</option>
                    @endforeach
                </select>
                <p class="text-red-500 text-xs italic mt-1 js-error-message" data-field="{{ $namePrefix }}.attribute_values"></p>
            </div>
        @endforeach
    </div>
</div>