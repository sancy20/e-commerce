@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Product Details</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Products</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">ID:</strong> {{ $product->id }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Name:</strong> {{ $product->name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Price:</strong> ${{ number_format($product->price, 2) }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">SKU:</strong> {{ $product->sku ?? 'N/A' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Stock Quantity:</strong> {{ $product->stock_quantity }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Is Featured:</strong>
                        @if ($product->is_featured)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                        @endif
                    </p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Created At:</strong> {{ $product->created_at->format('M d, Y H:i A') }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Updated At:</strong> {{ $product->updated_at->format('M d, Y H:i A') }}</p>
                </div>
            </div>
            <div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Description:</strong></p>
                    <p class="text-gray-600 mt-1">{{ $product->description ?? 'No description provided.' }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-gray-700"><strong class="font-semibold">Product Image:</strong></p>
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="mt-2 w-48 h-48 object-contain border rounded shadow-sm">
                    @else
                        <p class="text-gray-500">No image uploaded.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center mt-6">
            <a href="{{ route('admin.products.edit', $product->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Edit</a>
            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
            </form>
        </div>
    </div>
@endsection