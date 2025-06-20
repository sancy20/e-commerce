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
            <div class="mt-6 mb-4">
                <label class="block text-sm font-bold mb-2">Base Product Image:</label>
                 @if ($product->image)
                    <div class="mb-2">
                        <p class="text-xs">Current:</p>
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-24 w-24 object-cover rounded">
                        <label class="block mt-2 text-sm"><input type="checkbox" name="remove_base_image" value="1" class="mr-1"> Remove current image</label>
                    </div>
                @endif
                <input type="file" name="image" class="shadow w-full">
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
    const addVariantBtn = document.getElementById('add-variant-btn');
    const variantsContainer = document.getElementById('variants-container');
    const allAttributes = @json($attributes->load('values'));
    let newVariantIndex = 0;

    function createVariantRowHtml(isNew, data = {}) {
        const index = isNew ? `new_${newVariantIndex}` : data.id;
        const namePrefix = isNew ? `new_variants_data[${index}]` : `existing_variants[${data.id}]`;
        let hiddenIdInput = isNew ? '' : `<input type="hidden" name="${namePrefix}[id]" value="${data.id}">`;
        
        let attributesHtml = '';
        allAttributes.forEach(attribute => {
            // UPDATE: Generate checkboxes for variants
            let checkboxesHtml = '';
            attribute.values.forEach(value => {
                let isChecked = data.attribute_values && data.attribute_values.some(av => av.id === value.id);
                checkboxesHtml += `<label class="inline-flex items-center mr-4"><input type="checkbox" name="${namePrefix}[attribute_values][${attribute.id}][]" value="${value.id}" class="form-checkbox" ${isChecked ? 'checked' : ''}><span class="ml-2">${value.value}</span></label>`;
            });
            attributesHtml += `<div class="flex-1"><label class="block text-sm font-bold mb-2">${attribute.name}:</label><div class="flex flex-wrap">${checkboxesHtml}</div></div>`;
        });
        
        let imagePreviewHtml = data.image ? `<div class="mt-2"><p class="text-xs">Current: <img src="{{ asset('storage') }}/${data.image}" class="h-10 w-10 inline-block ml-2"></p><label class="block mt-1 text-sm"><input type="checkbox" name="${namePrefix}[remove_image]" value="1" class="mr-1"> Remove</label></div>` : '';
        return `<div class="variant-row border p-4 rounded-lg mb-4 bg-gray-50" data-variant-id="${data.id || 'new'}">${hiddenIdInput}<div class="flex justify-between items-center mb-4"><h3 class="text-lg font-semibold">Variant Details</h3><button type="button" class="remove-variant-btn text-red-500 font-bold">Remove</button></div><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div class="col-span-full"><div class="flex flex-col space-y-2">${attributesHtml}</div></div><div><label class="block text-sm font-bold mb-2">Price:</label><input type="number" name="${namePrefix}[price]" step="0.01" class="shadow w-full" value="${data.price || ''}" required></div><div><label class="block text-sm font-bold mb-2">SKU:</label><input type="text" name="${namePrefix}[sku]" class="shadow w-full" value="${data.sku || ''}"></div><div><label class="block text-sm font-bold mb-2">Stock:</label><input type="number" name="${namePrefix}[stock_quantity]" class="shadow w-full" value="${data.stock_quantity || 0}" required></div><div class="col-span-full"><label class="block text-sm font-bold mb-2">Image:</label><input type="file" name="${namePrefix}[image]" class="shadow w-full">${imagePreviewHtml}</div></div></div>`;
    }

    function addRow(isNew, data = {}) {
        variantsContainer.insertAdjacentHTML('beforeend', createVariantRowHtml(isNew, data));
        if (isNew) newVariantIndex++;
    }

    addVariantBtn.addEventListener('click', () => addRow(true, { attribute_values: [] }));

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

    const oldExisting = @json(old('existing_variants'));
    const oldNew = @json(old('new_variants_data'));
    if (oldExisting || oldNew) {
        if(oldExisting) Object.values(oldExisting).forEach(v => {
            v.attribute_values = Object.entries(v.attribute_values||{}).flatMap(([_,vals])=>Array.isArray(vals)?vals.map(valId=>({id:parseInt(valId)})):[] );
            addRow(false, v);
        });
        if(oldNew) Object.values(oldNew).forEach(v => {
            v.attribute_values = Object.entries(v.attribute_values||{}).flatMap(([_,vals])=>Array.isArray(vals)?vals.map(valId=>({id:parseInt(valId)})):[] );
            addRow(true, v);
        });
    } else {
        @json($product->variants->load('attributeValues')).forEach(v => addRow(false, v));
    }
});
</script>
@endpush