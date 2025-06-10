@extends('layouts.vendor')

@section('title', 'Edit Product')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Product: {{ $product->name }}</h1>
        <a href="{{ route('vendor.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to My Products</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('vendor.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Base Product Fields --}}
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Base Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('category_id') border-red-500 @enderror" required>
                        <option value="">Select a Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Base Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('price') border-red-500 @enderror" value="{{ old('price', $product->price) }}" required>
                    @error('price')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">Base SKU (Optional):</label>
                    <input type="text" name="sku" id="sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sku') border-red-500 @enderror" value="{{ old('sku', $product->sku) }}">
                    @error('sku')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Base Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('stock_quantity') border-red-500 @enderror" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    @error('stock_quantity')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" class="mr-2 leading-tight" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                    <label for="is_featured" class="text-gray-700 text-sm font-bold">Is Featured Product?</label>
                </div>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                <textarea name="description" id="description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Base Product Image:</label>
                @if ($product->image)
                    <div class="mb-2">
                        <p class="text-gray-600 text-sm">Current Image:</p>
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-24 w-24 object-cover rounded">
                        <label class="block mt-2 text-gray-700 text-sm">
                            <input type="checkbox" name="remove_base_image" value="1" class="mr-1"> Remove current base image
                        </label>
                    </div>
                @endif
                <input type="file" name="image" id="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('image') border-red-500 @enderror">
                @error('image')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- Product Variants Section --}}
            <h2 class="text-xl font-semibold text-gray-800 mt-8 mb-4 border-t pt-4">Product Variants</h2>
            <p class="text-gray-600 text-sm mb-4">Define specific versions of your product based on attributes like Color, Size etc. Each variant can have its own price, SKU, and stock.</p>

            <div id="variants-container">
                {{-- Existing Variants (from database or old input) --}}
                @foreach (old('existing_variants', $product->variants->load('attributeValues')) as $index => $variant)
                    @php
                        // Ensure $variant is an object for consistent access
                        $variantData = (object)$variant;
                        if (!isset($variantData->attributeValues)) { // Corrected check
                            $variantData->attribute_values = []; // Ensure attribute_values array exists
                        } else {
                            $variantData->attribute_values = $variantData->attributeValues->pluck('id')->toArray();
                        }
                        $namePrefix = "existing_variants[{$index}]"; // PHP version of name prefix
                        $displayIndex = $loop->iteration; // Human-readable index for existing
                        $selectedAttributeValueIds = $variantData->attribute_values;
                    @endphp
                    @include('vendor.products._variant_form_row', [
                        'index' => $index,
                        'variant' => $variantData, // Use the prepared variantData
                        'attributes' => $attributes,
                        'is_new' => false,
                        'errors' => $errors,
                        'namePrefix' => $namePrefix,
                        'displayIndex' => $displayIndex,
                        'selectedAttributeValueIds' => $selectedAttributeValueIds,
                    ])
                @endforeach

                {{-- New Variants (from old input on validation failure) --}}
                @if (old('new_variants_data'))
                    @foreach (old('new_variants_data') as $index => $newVariantData)
                        @php
                            $variant = (object)$newVariantData;
                            $namePrefix = "new_variants_data[{$index}]"; // PHP version of name prefix
                            $displayIndex = $loop->iteration; // Human-readable index for new variants
                            $selectedAttributeValueIds = (array)($variant->attribute_values ?? []);
                        @endphp
                        @include('vendor.products._variant_form_row', [
                            'index' => 'new_' . $index, // Prefix new variants to avoid ID conflicts
                            'variant' => $variant,
                            'attributes' => $attributes,
                            'is_new' => true,
                            'errors' => $errors,
                            'namePrefix' => $namePrefix,
                            'displayIndex' => $displayIndex,
                            'selectedAttributeValueIds' => $selectedAttributeValueIds,
                        ])
                    @endforeach
                @endif
            </div>

            <button type="button" id="add-new-variant-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-4">
                Add New Variant
            </button>

            <div class="flex items-center justify-end mt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Product</button>
            </div>
        </form>
    </div>

    {{-- HIDDEN TEMPLATE FOR NEW VARIANT ROWS --}}
    {{-- This template is used by JavaScript to clone and add new variant rows --}}
    <template id="variant-row-template" style="display: none;">
        @include('vendor.products._dynamic_variant_row_template', [
            'index' => 'JS_INDEX_PLACEHOLDER', // Placeholder for JS index
            'variant' => (object)([]), // Empty object for new variant
            'attributes' => $attributes,
            'is_new' => true,
            'errors' => new Illuminate\Support\ViewErrorBag(), // Pass an empty error bag for new rows
        ])
    </template>
@endsection

@push('scripts')
<script>
    console.log('Vendor Edit Product Script Loaded!'); // Debugging

    const addNewVariantBtn = document.getElementById('add-new-variant-btn');
    const variantsContainer = document.getElementById('variants-container');
    const variantRowTemplate = document.getElementById('variant-row-template');
    const attributes = @json($attributes);
    const laravelErrors = @json($errors->messages());

    // Initialize newVariantIndex based on total variants already rendered by Blade
    let newVariantIndex = {{ count(old('existing_variants', $product->variants)) + (old('new_variants_data') ? count(old('new_variants_data')) : 0) }};

    // Event listener for adding new variant rows
    addNewVariantBtn.addEventListener('click', () => {
        addVariantRow(newVariantIndex, true, {}); // Call with current index, true for isNew, empty initialData
        newVariantIndex++; // Increment for the next new variant
    });

    // Function to add a single variant row (used for new and existing)
    function addVariantRow(index, isNew, initialData = {}) {
        const templateContent = variantRowTemplate.content.cloneNode(true);
        const newRowDiv = templateContent.firstElementChild;

        const namePrefix = isNew ? `new_variants_data[${index}]` : `existing_variants[${index}]`;
        const displayIndex = parseInt(index) + 1; // For human readable number, handle 'new_X' prefix

        // Replace all JS_INDEX_PLACEHOLDER
        let htmlString = newRowDiv.outerHTML;
        htmlString = htmlString.replace(/JS_INDEX_PLACEHOLDER/g, index);
        htmlString = htmlString.replace(/JS_DISPLAY_INDEX_PLACEHOLDER/g, displayIndex);

        // Replace data-variant-index and data-is-new attributes
        htmlString = htmlString.replace(`data-variant-index="JS_INDEX_PLACEHOLDER"`, `data-variant-index="${index}"`);
        htmlString = htmlString.replace(`data-is-new="true"`, `data-is-new="${isNew}"`); // Ensure it reflects isNew correctly

        // Handle hidden ID input for existing variants
        if (!isNew && initialData.id) {
            const hiddenIdInput = `<input type="hidden" name="<span class="math-inline">\{namePrefix\}\[id\]" value\="</span>{initialData.id}">`;
            const h3TagRegex = /(<h3[^>]*>.*?<\/h3>)/;
            htmlString = htmlString.replace(h3TagRegex, `<span class="math-inline">1</span>{hiddenIdInput}`);
        }

        // Replace dynamic values (sku, price, stock_quantity)
        htmlString = htmlString.replace(/JS_SKU_PLACEHOLDER/g, initialData.sku || '');
        htmlString = htmlString.replace(/JS_PRICE_PLACEHOLDER/g, initialData.price || '');
        htmlString = htmlString.replace(/JS_STOCK_QUANTITY_PLACEHOLDER/g, initialData.stock_quantity || '0');

        // Handle image preview for existing variants
        let imagePreviewHtml = '';
        if (initialData.image) {
            const imageUrl = isNew ? (initialData.image instanceof File ? URL.createObjectURL(initialData.image) : initialData.image) : `{{ asset('storage') }}/${initialData.image}`;
            imagePreviewHtml = `<p class="text-gray-600 text-xs mt-1">Current: <img src="${imageUrl}" class="h-10 w-10 object-cover inline-block ml-2"></p>`;
            if (!isNew) { // Add remove checkbox only for existing images
                imagePreviewHtml += `<label class="block mt-1 text-gray-700 text-sm"><input type="checkbox" name="${namePrefix}[remove_image]" value="1" class="mr-1"> Remove current image</label>`;
            }
        }
        htmlString = htmlString.replace('JS_IMAGE_PREVIEW_PLACEHOLDER', imagePreviewHtml);

        // Handle selected attribute values
        attributes.forEach(attribute => {
            attribute.values.forEach(value => {
                const placeholder = `JS_SELECTED_ATTRIBUTE_VALUE_${attribute.id}_${value.id}`;
                const isSelected = initialData.attribute_values && initialData.attribute_values.includes(value.id);
                if (isSelected) {
                    htmlString = htmlString.replace(placeholder, 'selected');
                } else {
                    htmlString = htmlString.replace(placeholder, '');
                }
            });
        });

        // Replace error messages (after all other replacements)
        const tempElement = document.createElement('div');
        tempElement.innerHTML = htmlString;
        tempElement.querySelectorAll('.js-error-message').forEach(errorP => {
            const dataField = errorP.dataset.field;
            const fieldNameForError = dataField.replace(/JS_INDEX_PLACEHOLDER/g, index);
            const fullErrorFieldName = fieldNameForError.replace('new_variants_data', namePrefix).replace('existing_variants', namePrefix);

            if (laravelErrors[fullErrorFieldName]) {
                errorP.textContent = laravelErrors[fullErrorFieldName][0];
                errorP.classList.remove('hidden');
            } else {
                errorP.textContent = '';
                errorP.classList.add('hidden');
            }
        });
        htmlString = tempElement.innerHTML;

        // Append the new row to the container
        const newRowWrapper = document.createElement('div');
        newRowWrapper.innerHTML = htmlString.trim();
        variantsContainer.appendChild(newRowWrapper.firstElementChild);
    }

    function getErrorMessage(fieldName) {
        const errors = @json($errors->messages());
        if (errors[fieldName]) {
            return `<p class="text-red-500 text-xs italic mt-1">${errors[fieldName][0]}</p>`;
        }
        return '';
    }

    variantsContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-variant-btn')) {
            const variantRow = event.target.closest('.variant-row');
            const isNew = variantRow.dataset.isNew === 'true';

            if (!isNew && variantRow.querySelector('input[name$="[id]"]')) {
                const variantId = variantRow.querySelector('input[name$="[id]"]').value;
                const hiddenDeleteInput = document.createElement('input');
                hiddenDeleteInput.type = 'hidden';
                hiddenDeleteInput.name = 'variants_to_delete[]';
                hiddenDeleteInput.value = variantId;
                variantsContainer.appendChild(hiddenDeleteInput);
            }
            variantRow.remove();
        }
    });
</script>
@endpush