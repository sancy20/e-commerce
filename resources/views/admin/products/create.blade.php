@extends('layouts.admin')

@section('title', 'Create New Product')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Create New Product</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Products</a>
    </div>

    @if(session('error') || !$errors->isEmpty())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') ?? 'Please correct the issues below.' }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2 class="text-xl font-semibold mb-4">Base Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Product Name --}}
                <div>
                    <label for="name" class="block text-sm font-bold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="shadow w-full p-2 border rounded" value="{{ old('name') }}" required>
                </div>
                {{-- Category Selection --}}
                <div>
                    <label for="category_id" class="block text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow w-full p-2 border rounded" required>
                        <option value="">Select a Category First</option>
                        @foreach ($mainCategories as $mainCategory)
                            <optgroup label="{{ $mainCategory->name }}">
                                @foreach ($mainCategory->children as $subCategory)
                                    <option value="{{ $subCategory->id }}" {{ old('category_id') == $subCategory->id ? 'selected' : '' }}>
                                        {{ $subCategory->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                {{-- Other base fields --}}
                <div>
                    <label for="price" class="block text-sm font-bold mb-2">Base Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow w-full p-2 border rounded" value="{{ old('price') }}" required>
                </div>
                <div>
                    <label for="sku" class="block text-sm font-bold mb-2">Base SKU:</label>
                    <input type="text" name="sku" id="sku" class="shadow w-full p-2 border rounded" value="{{ old('sku') }}">
                </div>
                <div>
                    <label for="stock_quantity" class="block text-sm font-bold mb-2">Base Stock:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow w-full p-2 border rounded" value="{{ old('stock_quantity', 0) }}" required>
                </div>
                 <div class="flex items-center self-end">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" class="mr-2" {{ old('is_featured') ? 'checked' : '' }}>
                    <label for="is_featured" class="text-sm font-bold">Is Featured?</label>
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-bold mb-2">Description:</label>
                <textarea name="description" id="description" rows="5" class="shadow w-full p-2 border rounded">{{ old('description') }}</textarea>
            </div>

            {{-- Image Gallery Section --}}
            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-2">Image Gallery</h3>
                <p class="text-xs text-gray-500 mb-4">Upload additional images for the product gallery.</p>
                <div>
                    <label class="block text-sm font-bold mb-2">Add New Images:</label>
                    <input type="file" name="gallery_images[]" multiple class="shadow w-full p-2 border rounded">
                </div>
            </div>

            {{-- Variants Section --}}
            <div id="variants-section" class="border-t pt-4 mt-4">
                <h2 class="text-xl font-semibold mb-4">Product Variants</h2>
                <div id="variants-container"></div>
                <button type="button" id="add-variant-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mt-4 disabled:bg-gray-400" disabled>Add New Variant</button>
            </div>

            <div class="flex justify-end mt-6 border-t pt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Create Product</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category_id');
    const variantsContainer = document.getElementById('variants-container');
    const addVariantBtn = document.getElementById('add-variant-btn');
    
    let categoryAttributes = []; 
    let newVariantIndex = 0;
    
    async function fetchAttributesForCategory(categoryId) {
        variantsContainer.innerHTML = '<p class="text-gray-500 text-sm">Loading attributes...</p>';
        addVariantBtn.disabled = true;

        if (!categoryId) {
            variantsContainer.innerHTML = '<p class="text-sm text-gray-500">Please select a product category to manage variants.</p>';
            return;
        }

        try {
            const response = await fetch(`/api/categories/${categoryId}/attributes`);
            if (!response.ok) throw new Error('Network response was not ok.');
            
            categoryAttributes = await response.json();

            variantsContainer.innerHTML = ''; 
            if (categoryAttributes.length > 0) {
                addVariantBtn.disabled = false;
            } else {
                variantsContainer.innerHTML = '<p class="text-sm text-gray-500">No attributes are configured for this category.</p>';
            }
            reRenderAllVariants(); // Re-render in case of old input
        } catch (error) {
            console.error('Error fetching attributes:', error);
            variantsContainer.innerHTML = '<p class="text-red-500 font-bold">Could not load attributes for this category.</p>';
        }
    }

    function createVariantRowHtml(isNew, data) {
        const index = isNew ? `new_${newVariantIndex}` : data.id;
        const namePrefix = `variants_data[${index}]`;
        let attributesHtml = '';

        if (categoryAttributes.length > 0) {
            categoryAttributes.forEach(attribute => {
                let optionsHtml = '';
                const radioGroupName = `${namePrefix}[attribute_values][${attribute.id}]`;
                
                attribute.values.forEach(value => {
                    const optionId = `variant_${index}_attr_${attribute.id}_val_${value.id}`;
                    let isChecked = data.attribute_values && data.attribute_values[attribute.id] == value.id;
                    optionsHtml += `<label for="${optionId}" class="inline-flex items-center mr-4 cursor-pointer"><input type="radio" id="${optionId}" name="${radioGroupName}" value="${value.id}" class="form-radio" ${isChecked ? 'checked' : ''}><span class="ml-2">${value.value}</span></label>`;
                });
                attributesHtml += `<div class="flex-1 mb-2"><div class="flex justify-between items-center"><label class="block text-sm font-bold mb-2">${attribute.name}:</label></div><div class="flex flex-wrap">${optionsHtml}</div></div>`;
            });
        }
        
        return `
        <div class="variant-row border p-4 rounded-lg mb-4 bg-gray-50">
            <div class="flex justify-between items-center mb-4"><h3 class="text-lg font-semibold">Variant Details</h3><button type="button" class="remove-variant-btn text-red-500 font-bold">Remove</button></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-full"><div class="flex flex-col space-y-2">${attributesHtml}</div></div>
                <div><label class="block text-sm font-bold mb-2">Price:</label><input type="number" name="${namePrefix}[price]" step="0.01" class="shadow w-full p-2 border rounded" value="${data.price || ''}" required></div>
                <div><label class="block text-sm font-bold mb-2">SKU:</label><input type="text" name="${namePrefix}[sku]" class="shadow w-full p-2 border rounded" value="${data.sku || ''}"></div>
                <div><label class="block text-sm font-bold mb-2">Stock:</label><input type="number" name="${namePrefix}[stock_quantity]" class="shadow w-full p-2 border rounded" value="${data.stock_quantity || 0}" required></div>
                <div class="col-span-full">
                    <label class="block text-sm font-bold mb-2">Variant-Specific Image (Optional):</label>
                    <input type="file" name="${namePrefix}[image]" class="shadow w-full p-2 border rounded">
                </div>
            </div>
        </div>`;
    }

    function addRow(isNew, data = {}) {
        variantsContainer.insertAdjacentHTML('beforeend', createVariantRowHtml(isNew, data));
        if (isNew) newVariantIndex++;
    }
    
    function reRenderAllVariants() {
        // This function repopulates variants from 'old' data if validation fails
        const oldVariants = @json(old('variants_data', []));
        variantsContainer.innerHTML = '';
        newVariantIndex = 0;
        oldVariants.forEach(variantData => addRow(true, variantData));
    }

    addVariantBtn.addEventListener('click', () => { 
        if (categoryAttributes.length > 0) {
            addRow(true);
        } else {
            alert('Please select a category with attributes before adding a variant.');
        }
    });
    
    variantsContainer.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-variant-btn')) {
            event.target.closest('.variant-row').remove();
        }
    });

    categorySelect.addEventListener('change', (event) => {
        fetchAttributesForCategory(event.target.value);
    });

    // Initial check on page load in case of validation errors
    if (categorySelect.value) {
        fetchAttributesForCategory(categorySelect.value);
    } else {
        variantsContainer.innerHTML = '<p class="text-sm text-gray-500">Please select a product category to manage variants.</p>';
    }
});
</script>
@endpush
