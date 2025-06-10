@extends('layouts.admin')

@section('title', 'Variant Details for ' . $product->name)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Variant Details: {{ $variant->variant_name }}</h1>
        <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Product</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Product:</strong> {{ $product->name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Variant Name:</strong> {{ $variant->variant_name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">SKU:</strong> {{ $variant->sku ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Price:</strong> ${{ number_format($variant->price ?? $product->price, 2) }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Stock Quantity:</strong> {{ $variant->stock_quantity }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Created At:</strong> {{ $variant->created_at->format('M d, Y H:i A') }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Updated At:</strong> {{ $variant->updated_at->format('M d, Y H:i A') }}</p>
                </div>
            </div>
            <div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Variant Image:</strong></p>
                    @if ($variant->image)
                        <img src="{{ asset('storage/' . $variant->image) }}" alt="Variant Image" class="mt-2 w-48 h-48 object-contain border rounded shadow-sm">
                    @else
                        <p class="text-gray-500">No image uploaded.</p>
                    @endif
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Attributes:</strong></p>
                    <ul class="list-disc list-inside">
                        @foreach ($variant->attributeValues as $attrValue)
                            <li>{{ $attrValue->attribute->name ?? 'N/A' }}: {{ $attrValue->value }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <a href="{{ route('admin.products.variants.edit', [$product->id, $variant->id]) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Edit Variant</a>
            <form action="{{ route('admin.products.variants.destroy', [$product->id, $variant->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this variant?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Variant</button>
            </form>
        </div>
    </div>
@endsection