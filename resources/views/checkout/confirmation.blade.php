@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
        <h1 class="text-4xl font-bold text-green-600 mb-4">Order Placed Successfully!</h1>
        <p class="text-lg text-gray-700 mb-6">Thank you for your order. Your order number is:</p>
        <p class="text-5xl font-extrabold text-blue-700 mb-8">{{ $order->order_number }}</p>

        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Order Details</h2>
            <div class="text-left max-w-xl mx-auto mb-6">
                <p class="text-gray-700 mb-2"><strong class="font-semibold">Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                <p class="text-gray-700 mb-2"><strong class="font-semibold">Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p class="text-gray-700 mb-2"><strong class="font-semibold">Shipping Address:</strong> {{ $order->shipping_address }}</p>
                <p class="text-gray-700 mb-2"><strong class="font-semibold">Order Status:</strong> {{ ucfirst($order->order_status) }}</p>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-3">Items in Your Order:</h3>
            <div class="text-left max-w-2xl mx-auto">
                @foreach ($order->orderItems as $item)
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-center">
                            @php
                                $image = ($item->variant && $item->variant->image) 
                                    ? $item->variant->image 
                                    : ($item->product->image ?? null);
                            @endphp
                            @if ($image)
                                <img src="{{ asset('storage/' . $image) }}" alt="{{ $item->product->name ?? 'Product image' }}" class="w-12 h-12 object-cover rounded-md mr-3">
                            @endif
                            <div>
                                <p class="font-medium">{{ $item->product->name ?? 'Product Not Found' }}</p>
                                
                                @if ($item->variant)
                                    <p class="text-sm text-gray-500">{{ $item->variant->variant_name }}</p>
                                @endif

                                <p class="text-sm text-gray-600">Qty: {{ $item->quantity }} x ${{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>
                        <span class="font-semibold">${{ number_format($item->quantity * $item->price, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ route('products.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Continue Shopping</a>
        </div>
    </div>
</div>
@endsection