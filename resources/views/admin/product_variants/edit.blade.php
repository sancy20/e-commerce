@extends('layouts.admin')

@section('title', 'Edit Product Variant')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Variant for: {{ $product->name }}</h1>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Product</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.products.variants.update', [$product->id, $variant->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- SKU --}}
                <div class="mb-4">
                    <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">Variant SKU (Optional):</label>
                    <input type="text" name="sku" id="sku" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sku') border-red-500 @enderror" value="{{ old('sku', $variant->sku) }}">
                    @error('sku')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price (Optional, overrides product price) --}}
                <div class="mb-4">
                    <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Variant Price (Optional, defaults to product price: ${{ number_format($product->price, 2) }}):</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('price') border-red-500 @enderror" value="{{ old('price', $variant->price) }}">
                    @error('price')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stock Quantity --}}
                <div class="mb-4">
                    <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('stock_quantity') border-red-500 @enderror" value="{{ old('stock_quantity', $variant->stock_quantity) }}" required>
                    @error('stock_quantity')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Variant Image (Optional) --}}
                <div class="mb-4">
                    <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Variant Image (Optional):</label>
                    @if ($variant->image)
                        <div class="mb-2">
                            <p class="text-gray-600 text-sm">Current Image:</p>
                            <img src="{{ asset('storage/' . $variant->image) }}" alt="Variant Image" class="h-24 w-24 object-cover rounded">
                            <label class="block mt-2 text-gray-700 text-sm">
                                <input type="checkbox" name="remove_image" value="1" class="mr-1"> Remove current image
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('image') border-red-500 @enderror">
                    @error('image')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-4">Define Variant Attributes</h3>
            <p class="text-gray-600 text-sm mb-4">Select the attribute values that uniquely define this variant (e.g., Color: Red, Size: Medium).</p>

            <div id="attributes-container">
                @foreach ($attributes as $attribute)
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                        <label class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                        <select name="attribute_values[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('attribute_values') border-red-500 @enderror">
                            <option value="">-- Select {{ $attribute->name }} --</option>
                            @foreach ($attribute->values as $value)
                                <option value="{{ $value->id }}" {{ in_array($value->id, old('attribute_values', $currentAttributeValueIds)) ? 'selected' : '' }}>
                                    {{ $value->value }}
                                </option>
                            @endforeach
                        </select>
                        @error('attribute_values')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Variant</button>
            </div>
        </form>
    </div>
@endsection