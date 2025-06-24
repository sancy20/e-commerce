@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Product: {{ $product->name }}</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Products</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h2 class="text-xl font-semibold text-gray-800 mb-4">Base Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('name', $product->name) }}" required>
                    @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow w-full" required>
                        <option value="">Select a Category</option>
                        @foreach ($mainCategories as $mainCategory)
                            <optgroup label="{{ $mainCategory->name }}">
                                @foreach ($mainCategory->children as $subCategory)
                                    <option value="{{ $subCategory->id }}" {{ old('category_id', $product->category_id) == $subCategory->id ? 'selected' : '' }}>
                                        {{ $subCategory->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Base Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('price', $product->price) }}" required>
                    @error('price') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">Base SKU (Optional):</label>
                    <input type="text" name="sku" id="sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('sku', $product->sku) }}">
                    @error('sku') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Base Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    @error('stock_quantity') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" class="mr-2 leading-tight" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                    <label for="is_featured" class="text-gray-700 text-sm font-bold">Is Featured Product?</label>
                </div>
            </div>
<!-- 
            {{-- UPDATE: Changed from multi-select to checkboxes --}}
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2">Product Attributes</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @php
                        $productAttributeValueIds = $product->attributeValues ? $product->attributeValues->pluck('id') : collect([]);
                    @endphp
                    @foreach ($attributes as $attribute)
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                             <div class="flex flex-col">
                                @foreach ($attribute->values as $value)
                                    <label class="inline-flex items-center mt-1">
                                        <input type="checkbox" name="base_attribute_values[{{ $attribute->id }}][]" value="{{ $value->id }}" class="form-checkbox"
                                            {{ (is_array(old('base_attribute_values.'.$attribute->id)) && in_array($value->id, old('base_attribute_values.'.$attribute->id))) || $productAttributeValueIds->contains($value->id) ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700">{{ $value->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div> -->

            <div class="mt-6"><label class="block text-sm font-bold mb-2">Description:</label><textarea name="description" rows="5" class="shadow w-full">{{ old('description', $product->description) }}</textarea></div>
            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-2">Image Gallery</h3>
                <p class="text-xs text-gray-500 mb-4">Upload additional images for the product gallery.</p>
                
                {{-- Display existing gallery images with delete checkboxes --}}
                <div class="flex flex-wrap gap-4 mb-4">
                    @foreach($product->images as $image)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $image->path) }}" class="h-24 w-24 object-cover rounded">
                            <label class="absolute top-0 right-0 -mt-2 -mr-2">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="form-checkbox h-5 w-5 text-red-600">
                                <span class="text-xs text-red-600 font-bold">X</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                {{-- Field for uploading new gallery images --}}
                <div>
                    <label class="block text-sm font-bold mb-2">Add New Images:</label>
                    <input type="file" name="gallery_images[]" multiple class="shadow w-full p-2 border rounded">
                </div>
            </div>

            <h2 class="text-xl font-semibold mt-8 mb-4 border-t pt-4">Product Variants</h2>
            <div id="variants-container"></div>
            <button type="button" id="add-variant-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mt-4">Add New Variant</button>
            <div class="flex justify-end mt-6"><button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Update Product</button></div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category_id');
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn = document.getElementById('add-variant-btn');
    
    // CORRECTED: This variable will hold the attributes loaded via API for the selected category.
    let categoryAttributes = []; 
    let newVariantIndex = 0;
    
    const requestStoreUrl = '{{ route('vendor.attributes.request.store') }}';
    const csrfToken = '{{ csrf_token() }}';

    async function fetchAttributesForCategory(categoryId) {
        variantsContainer.innerHTML = '<p class="text-gray-500">Loading attributes...</p>';
        addVariantBtn.disabled = true;

        if (!categoryId) {
            variantsContainer.innerHTML = '<p class="text-sm text-gray-500">Please select a product category to manage variants.</p>';
            return;
        }

        try {
            // This API route must exist in your web.php or api.php file.
            const response = await fetch(`/api/categories/${categoryId}/attributes`);
            if (!response.ok) throw new Error('Network response was not ok.');
            
            categoryAttributes = await response.json();

            variantsContainer.innerHTML = ''; // Clear loading message
            if (categoryAttributes.length > 0) {
                addVariantBtn.disabled = false;
            } else {
                variantsContainer.innerHTML = '<p class="text-sm text-gray-500">No attributes are configured for this category. You can still save the base product.</p>';
            }
            
            reRenderAllVariants();

        } catch (error) {
            console.error('Error fetching attributes:', error);
            variantsContainer.innerHTML = '<p class="text-red-500 font-bold">Could not load attributes for this category.</p><p class="text-xs text-gray-600">Please ensure the API route is correct and the server is running.</p>';
        }
    }

    function createVariantRowHtml(isNew, data) {
        const index = isNew ? `new_${newVariantIndex}` : data.id;
        const namePrefix = isNew ? `new_variants_data[${index}]` : `existing_variants[${data.id}]`;
        let attributesHtml = '';

        if (categoryAttributes.length > 0) {
            categoryAttributes.forEach(attribute => {
                let optionsHtml = '';
                const radioGroupName = `${namePrefix}[attribute_values][${attribute.id}]`;
                
                const approvedValues = attribute.values.filter(v => v.is_approved);

                approvedValues.forEach(value => {
                    const optionId = `variant_${index}_attr_${attribute.id}_val_${value.id}`;
                    let isChecked = data.attribute_values && data.attribute_values.some(av => av.id === value.id);
                    optionsHtml += `<label for="${optionId}" class="inline-flex items-center mr-4 cursor-pointer"><input type="radio" id="${optionId}" name="${radioGroupName}" value="${value.id}" class="form-radio" ${isChecked ? 'checked' : ''}><span class="ml-2">${value.value}</span></label>`;
                });

                attributesHtml += `<div class="flex-1 mb-2"><div class="flex justify-between items-center"><label class="block text-sm font-bold mb-2">${attribute.name}:</label><a href="#" class="text-xs text-blue-500 hover:underline" onclick="event.preventDefault(); requestNewValue(${attribute.id}, '${attribute.name}');">+ Request New</a></div><div class="flex flex-wrap">${optionsHtml}</div></div>`;
            });
        }
        
        const imagePreviewHtml = data.image ? `<div class="mt-2"><p class="text-xs">Current: <img src="{{ asset('storage') }}/${data.image}" class="h-10 w-10 inline-block ml-2 object-cover"></p><label class="block mt-1 text-sm"><input type="checkbox" name="${namePrefix}[remove_image]" value="1" class="mr-1"> Remove</label></div>` : '';

        return `
        <div class="variant-row border p-4 rounded-lg mb-4 bg-gray-50" data-variant-id="${data.id || 'new'}">
            ${isNew ? '' : `<input type="hidden" name="existing_variants[${data.id}][id]" value="${data.id}">`}
            <div class="flex justify-between items-center mb-4"><h3 class="text-lg font-semibold">Variant Details</h3><button type="button" class="remove-variant-btn text-red-500 font-bold hover:text-red-700">Remove</button></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-full"><div class="flex flex-col space-y-2">${attributesHtml}</div></div>
                <div><label class="block text-sm font-bold mb-2">Price:</label><input type="number" name="${namePrefix}[price]" step="0.01" class="shadow w-full p-2 border rounded" value="${data.price || ''}" required></div>
                <div><label class="block text-sm font-bold mb-2">SKU:</label><input type="text" name="${namePrefix}[sku]" class="shadow w-full p-2 border rounded" value="${data.sku || ''}"></div>
                <div><label class="block text-sm font-bold mb-2">Stock:</label><input type="number" name="${namePrefix}[stock_quantity]" class="shadow w-full p-2 border rounded" value="${data.stock_quantity || 0}" required></div>
                <div class="col-span-full">
                    <label class="block text-sm font-bold mb-2">Variant-Specific Image (Optional):</label>
                    <input type="file" name="${namePrefix}[image]" class="shadow w-full p-2 border rounded">
                    ${imagePreviewHtml}
                </div>
            </div>
        </div>`;
    }

    window.requestNewValue = function(attributeId, attributeName) {
        const newValue = prompt(`Enter the new value you would like to request for the attribute "${attributeName}":`);
        if (newValue && newValue.trim() !== '') {
            fetch(requestStoreUrl, { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ attribute_id: attributeId, value: newValue })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success! Your request has been submitted for approval.');
                } else {
                    alert('Error: ' + (data.message || 'Could not submit request.'));
                }
            })
            .catch(error => console.error('Request Error:', error));
        }
    }

    function addRow(isNew, data = {}) {
        variantsContainer.insertAdjacentHTML('beforeend', createVariantRowHtml(isNew, data));
        if (isNew) newVariantIndex++;
    }
    
    function reRenderAllVariants() {
        const existingVariantsData = @json($product->variants->load('attributeValues'));
        const oldExisting = @json(old('existing_variants'));
        const oldNew = @json(old('new_variants_data'));

        variantsContainer.innerHTML = '';
        newVariantIndex = 0;

        if (oldExisting || oldNew) {
            if (oldExisting) Object.values(oldExisting).forEach(v => addRow(false, v));
            if (oldNew) Object.values(oldNew).forEach(v => addRow(true, v));
        } else {
            existingVariantsData.forEach(variant => addRow(false, variant));
        }
    }

    addVariantBtn.addEventListener('click', () => { 
        if (categoryAttributes.length > 0) {
            addRow(true, { attribute_values: [] });
        } else {
            alert('Please select a category with attributes before adding a variant.');
        }
    });
    
    variantsContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-variant-btn')) {
            const variantRow = event.target.closest('.variant-row');
            const variantId = variantRow.dataset.variantId;
            if (variantId && variantId !== 'new') {
                const hiddenDeleteInput = document.createElement('input');
                hiddenDeleteInput.type = 'hidden';
                hiddenDeleteInput.name = 'variants_to_delete[]';
                hiddenDeleteInput.value = variantId;
                variantsContainer.appendChild(hiddenDeleteInput);
            }
            variantRow.remove();
        }
    });

    categorySelect.addEventListener('change', (event) => {
        fetchAttributesForCategory(event.target.value);
    });

    if (categorySelect.value) {
        fetchAttributesForCategory(categorySelect.value);
    } else {
        variantsContainer.innerHTML = '<p class="text-sm text-gray-500">Please select a product category to manage variants.</p>';
    }
});
</script>
@endpush