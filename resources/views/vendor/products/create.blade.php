@extends('layouts.vendor')
@section('title', 'Add New Product')
@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Create New Product</h1>
        <a href="{{ route('vendor.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Products</a>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Base Product Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold mb-2">Product Name:</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('name') }}" required>
                    @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-bold mb-2">Category:</label>
                    <select name="category_id" id="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                        <option value="">Select a Category</option>
                        {{-- UPDATE: Group categories for better UX --}}
                        @foreach ($categories as $mainCategory)
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
                <div>
                    <label for="price" class="block text-sm font-bold mb-2">Base Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('price') }}" required>
                    @error('price') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="block text-sm font-bold mb-2">Base SKU:</label>
                    <input type="text" name="sku" id="sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('sku') }}">
                    @error('sku') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stock_quantity" class="block text-sm font-bold mb-2">Base Stock:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('stock_quantity', 0) }}" required>
                    @error('stock_quantity') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" id="is_featured" class="mr-2 leading-tight" {{ old('is_featured') ? 'checked' : '' }}>
                    <label for="is_featured" class="text-sm font-bold">Is Featured Product?</label>
                </div>
            </div>

            <!-- <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Product Attributes (for Simple Products)</h3>
                <p class="text-gray-600 text-xs mb-4">Select the specific attributes for this base product. These are ignored if you add variants below.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($attributes as $attribute)
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                            <div class="flex flex-col">
                                @foreach ($attribute->values as $value)
                                    <label class="inline-flex items-center mt-1">
                                        <input type="checkbox" name="base_attribute_values[{{ $attribute->id }}][]" value="{{ $value->id }}" class="form-checkbox"
                                            {{ (is_array(old('base_attribute_values.'.$attribute->id)) && in_array($value->id, old('base_attribute_values.'.$attribute->id))) ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700">{{ $value->value }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div> -->

            <div class="mt-6"><label class="block text-sm font-bold mb-2">Description:</label><textarea name="description" rows="5" class="shadow w-full">{{ old('description') }}</textarea></div>
            <div class="mt-6 mb-4"><label class="block text-sm font-bold mb-2">Base Image:</label><input type="file" name="image" class="shadow w-full"></div>

            <h2 class="text-xl font-semibold mt-8 mb-4 border-t pt-4">Product Variants (Optional)</h2>
            <div id="variants-container"></div>
            <button type="button" id="add-variant-btn" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mt-4">Add Variant</button>
            <div class="flex justify-end mt-6"><button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Create Product</button></div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addVariantBtn = document.getElementById('add-variant-btn');
    const variantsContainer = document.getElementById('variants-container');
    const allAttributes = @json($attributes->load('values'));
    let variantIndex = 0;

    function createVariantRowHtml(index, data = {}) {
        let attributesHtml = '';
        allAttributes.forEach(attribute => {
            // UPDATE: Generate checkboxes instead of a select dropdown for variants
            let checkboxesHtml = '';
            attribute.values.forEach(value => {
                const isChecked = (data.attribute_values && data.attribute_values[attribute.id] && data.attribute_values[attribute.id].includes(value.id.toString())) ? 'checked' : '';
                checkboxesHtml += `<label class="inline-flex items-center mr-4"><input type="checkbox" name="variants_data[${index}][attribute_values][${attribute.id}][]" value="${value.id}" class="form-checkbox" ${isChecked}><span class="ml-2">${value.value}</span></label>`;
            });
            attributesHtml += `<div class="flex-1"><label class="block text-sm font-bold mb-2">${attribute.name}:</label><div class="flex flex-wrap">${checkboxesHtml}</div></div>`;
        });
        return `<div class="variant-row border p-4 rounded-lg mb-4 bg-gray-50"><div class="flex justify-between items-center mb-4"><h3 class="text-lg font-semibold">Variant #${index + 1}</h3><button type="button" class="remove-variant-btn text-red-500 font-bold">Remove</button></div><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div class="col-span-full"><div class="flex flex-col space-y-2">${attributesHtml}</div></div><div><label class="block text-sm font-bold mb-2">Price:</label><input type="number" name="variants_data[${index}][price]" step="0.01" class="shadow w-full" value="${data.price || ''}" required></div><div><label class="block text-sm font-bold mb-2">SKU:</label><input type="text" name="variants_data[${index}][sku]" class="shadow w-full" value="${data.sku || ''}"></div><div><label class="block text-sm font-bold mb-2">Stock:</label><input type="number" name="variants_data[${index}][stock_quantity]" class="shadow w-full" value="${data.stock_quantity || 0}" required></div><div class="col-span-full"><label class="block text-sm font-bold mb-2">Image:</label><input type="file" name="variants_data[${index}][image]" class="shadow w-full"></div></div></div>`;
    }
    
    function addRow(data = {}) {
        variantsContainer.insertAdjacentHTML('beforeend', createVariantRowHtml(variantIndex, data));
        variantIndex++;
    }
    
    addVariantBtn.addEventListener('click', () => addRow());
    variantsContainer.addEventListener('click', e => { if(e.target.classList.contains('remove-variant-btn')) e.target.closest('.variant-row').remove(); });
    @json(old('variants_data', [])).forEach(addRow);
});
</script>
@endpush