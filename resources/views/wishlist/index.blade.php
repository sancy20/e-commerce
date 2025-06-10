@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Wishlist</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('info') }}
        </div>
    @endif

    @if ($wishlists->isEmpty())
        <p class="text-lg text-gray-600">Your wishlist is empty. Start adding some products!</p>
        <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Continue Shopping</a>
    @else
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added On</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($wishlists as $wishlist)
                            @php
                                $product = $wishlist->product;
                                $variant = $wishlist->productVariant;
                                $displayPrice = $variant ? ($variant->price ?? $product->price) : $product->price;
                                $displayStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
                                $displayName = $product->name . ($variant ? ' (' . $variant->variant_name . ')' : '');
                                $displayImage = $variant ? ($variant->image ?? $product->image) : $product->image;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap flex items-center">
                                    @if ($displayImage)
                                        <img src="{{ asset('storage/' . $displayImage) }}" alt="{{ $displayName }}" class="h-12 w-12 object-cover rounded-full mr-4">
                                    @endif
                                    <a href="{{ route('products.show', $product->slug) }}" class="text-gray-900 hover:underline">{{ $displayName }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">${{ number_format($displayPrice, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    @if ($displayStock > 0)
                                        <span class="text-green-600">{{ $displayStock }} in stock</span>
                                    @else
                                        <span class="text-red-600">Out of Stock</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $wishlist->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="{{ route('wishlist.move_to_cart', $wishlist->id) }}" method="POST" class="inline-block mr-2" onsubmit="return confirm('Move this item to cart?');">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-900" @if ($displayStock <= 0) disabled @endif>Move to Cart</button>
                                    </form>
                                    <form action="{{ route('wishlist.remove', $wishlist->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this item from your wishlist?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection