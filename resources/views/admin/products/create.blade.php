@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Create New Product</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Products</a>
    </div>

    {{-- Display a summary of validation errors if any exist --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please correct the issues below.</span>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Base Product Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Product Name --}}
                <div>
                    <label for="name" class="block text-sm font-bold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('name') }}" required>
                    @error('name') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Category Selection (Corrected) --}}
                <div>
                    <label for="category_id" class="block text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                        <option value="">Select a Category</option>
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
                    @error('category_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Base Price --}}
                <div>
                    <label for="price" class="block text-sm font-bold mb-2">Base Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('price') }}" required>
                    @error('price') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Base SKU --}}
                <div>
                    <label for="sku" class="block text-sm font-bold mb-2">Base SKU:</label>
                    <input type="text" name="sku" id="sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('sku') }}">
                    @error('sku') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Base Stock --}}
                <div>
                    <label for="stock_quantity" class="block text-sm font-bold mb-2">Base Stock:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('stock_quantity', 0) }}" required>
                    @error('stock_quantity') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Is Featured --}}
                <div class="flex items-center self-end">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" class="form-checkbox mr-2 leading-tight" {{ old('is_featured') ? 'checked' : '' }}>
                    <label for="is_featured" class="text-sm font-bold">Is Featured?</label>
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-6">
                <label for="description" class="block text-sm font-bold mb-2">Description:</label>
                <textarea name="description" id="description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">{{ old('description') }}</textarea>
                 @error('description') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Base Image --}}
            <div class="mt-6 mb-4">
                <label for="image" class="block text-sm font-bold mb-2">Base Image:</label>
                <input type="file" name="image" id="image" class="shadow w-full p-2 border rounded">
                @error('image') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
            </div>


            {{-- Variants Section --}}
            <div class="border-t pt-6 mt-6">
                 <h2 class="text-xl font-semibold mb-2">Product Variants (Optional)</h2>
                <p class="text-gray-600 text-xs mb-4">Add variants if the product comes in different options like size or color. If you add variants, the Base Price, SKU, and Stock will be ignored.</p>
                <div id="variants-container">
                    {{-- Variant rows will be injected here by JavaScript --}}
                </div>
                <button type="button" id="add-variant-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mt-4">Add Variant</button>
            </div>


            {{-- Submit Button --}}
            <div class="flex justify-end mt-8 border-t pt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg">Create Product</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addVariantBtn = document.getElementById('add-variant-btn');
    const variantsContainer = document.getElementById('variants-container');
    const allAttributes = @json($attributes ?? []);
    let variantIndex = 0;

    function createVariantRowHtml(index, data = {}) {
        let attributesHtml = '';
        if (allAttributes.length > 0) {
            allAttributes.forEach(attribute => {
                let optionsHtml = '';
                // Create a unique name for the radio button group for this attribute within this variant row
                const radioGroupName = `variants_data[${index}][attribute_values][${attribute.id}]`;
                
                attribute.values.forEach(value => {
                    const optionId = `variant_${index}_attr_${attribute.id}_val_${value.id}`;
                    // Check if this value was part of the old input for this variant
                    const isChecked = (data.attribute_values && data.attribute_values[attribute.id] == value.id) ? 'checked' : '';

                    optionsHtml += `
                        <label for="${optionId}" class="inline-flex items-center mr-4 cursor-pointer">
                            <input type="radio" id="${optionId}" name="${radioGroupName}" value="${value.id}" class="form-radio" ${isChecked}>
                            <span class="ml-2">${value.value}</span>
                        </label>`;
                });
                attributesHtml += `
                    <div class="flex-1 mb-2">
                        <label class="block text-sm font-bold mb-2">${attribute.name}:</label>
                        <div class="flex flex-wrap">${optionsHtml}</div>
                    </div>`;
            });
        } else {
            attributesHtml = '<p class="text-sm text-gray-500">No attributes have been configured. Please add attributes in the admin panel to create variants.</p>';
        }

        return `
        <div class="variant-row border p-4 rounded-lg mb-4 bg-gray-50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Variant Details</h3>
                <button type="button" class="remove-variant-btn text-red-500 font-bold hover:text-red-700">Remove</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-full">
                    <div class="flex flex-col space-y-2">${attributesHtml}</div>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Price:</label>
                    <input type="number" name="variants_data[${index}][price]" step="0.01" class="shadow w-full p-2 border rounded" value="${data.price || ''}" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">SKU:</label>
                    <input type="text" name="variants_data[${index}][sku]" class="shadow w-full p-2 border rounded" value="${data.sku || ''}">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2">Stock:</label>
                    <input type="number" name="variants_data[${index}][stock_quantity]" class="shadow w-full p-2 border rounded" value="${data.stock_quantity || 0}" required>
                </div>
                <div class="col-span-full">
                    <label class="block text-sm font-bold mb-2">Variant Image:</label>
                    <input type="file" name="variants_data[${index}][image]" class="shadow w-full p-2 border rounded">
                </div>
            </div>
        </div>`;
    }
    
    function addRow(data = {}) {
        variantsContainer.insertAdjacentHTML('beforeend', createVariantRowHtml(variantIndex, data));
        variantIndex++;
    }
    
    addVariantBtn.addEventListener('click', () => {
        if (allAttributes.length === 0) {
            alert('Cannot add variants because no product attributes (like Color or Size) are defined. Please add them in the admin panel first.');
            return;
        }
        addRow();
    });

    variantsContainer.addEventListener('click', e => {
        if (e.target.classList.contains('remove-variant-btn')) {
            e.target.closest('.variant-row').remove();
        }
    });

    // Re-populate form with old variant data if validation fails
    const oldVariants = @json(old('variants_data', []));
    if (oldVariants.length > 0) {
        oldVariants.forEach(variantData => addRow(variantData));
    }
});
</script>
@endpush