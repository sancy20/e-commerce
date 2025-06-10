@extends('layouts.app')

@section('title', 'Order ' . $order->order_number . ' Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Order #{{ $order->order_number }}</h1>
        <a href="{{ route('dashboard.orders') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Orders</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Order Summary --}}
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Order Information</h2>
                <p class="mb-2"><strong class="font-medium">Order Date:</strong> {{ $order->created_at->format('M d, Y H:i A') }}</p>
                <p class="mb-2"><strong class="font-medium">Total Amount:</strong> <span class="font-bold text-lg text-blue-700">${{ number_format($order->total_amount, 2) }}</span></p>
                <p class="mb-2"><strong class="font-medium">Order Status:</strong>
                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $order->order_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $order->order_status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->order_status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : '' }}
                        {{ $order->order_status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </p>
                <p class="mb-2"><strong class="font-medium">Payment Status:</strong>
                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $order->payment_status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $order->payment_status === 'refunded' ? 'bg-orange-100 text-orange-800' : '' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
                <p class="mb-2"><strong class="font-medium">Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p class="mb-2"><strong class="font-medium">Shipping Method:</strong> {{ $order->shippingMethod->name ?? 'N/A' }} (${{ number_format($order->shipping_cost, 2) }})</p>
                <p class="mb-2"><strong class="font-medium">Notes:</strong> {{ $order->notes ?? 'N/A' }}</p>
            </div>

            {{-- Shipping and Billing Addresses --}}
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Addresses</h2>
                <div class="mb-4">
                    <strong class="font-medium">Shipping Address:</strong>
                    <p class="text-gray-700">{{ $order->shipping_address }}</p>
                </div>
                <div>
                    <strong class="font-medium">Billing Address:</strong>
                    <p class="text-gray-700">{{ $order->billing_address ?? 'Same as shipping' }}</p>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ordered Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($order->orderItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap flex items-center">
                                    @if ($item->product->image ?? false)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name ?? 'Product' }}" class="h-10 w-10 object-cover rounded-full mr-3">
                                    @endif
                                    <a href="{{ route('products.show', $item->product->slug ?? '#') }}" class="text-sm font-medium text-gray-900 hover:underline">
                                        {{ $item->product->name ?? 'Product Not Found' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection