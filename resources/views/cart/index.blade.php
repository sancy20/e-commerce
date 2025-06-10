@extends('layouts.app')

@section('title', 'Your Shopping Cart')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Shopping Cart</h1>

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

    @if (empty($cartDetails))
        <p class="text-lg text-gray-600">Your cart is empty. Start shopping!</p>
        <a href="/" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Continue Shopping</a>
    @else
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($cartDetails as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap flex items-center">
                                    @if ($item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="h-12 w-12 object-cover rounded-full mr-4">
                                    @endif
                                    <span class="text-gray-900">{{ $item['name'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $item['sku'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">${{ number_format($item['price'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('cart.update') }}" method="POST" class="flex items-center">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="item_identifier" value="{{ $item['item_identifier'] }}"> {{-- Use item_identifier --}}
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                            class="form-input w-20 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <button type="submit" class="ml-2 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-sm">Update</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">${{ number_format($item['subtotal'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="{{ route('cart.remove', $item['item_identifier']) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this item?');">
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

            <div class="mt-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold">Total: ${{ number_format($total, 2) }}</h2>
                <div>
                    <form action="{{ route('cart.clear') }}" method="POST" class="inline-block mr-4" onsubmit="return confirm('Are you sure you want to clear your entire cart?');">
                        @csrf
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Clear Cart</button>
                    </form>
                    <a href="{{ route('checkout.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection