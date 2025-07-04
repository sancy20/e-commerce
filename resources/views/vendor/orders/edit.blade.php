@extends('layouts.vendor')

@section('title', 'Edit Order: ' . $order->order_number)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Order: {{ $order->order_number }}</h1>
        <a href="{{ route('vendor.orders.show', $order->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Order Details</a>
    </div>

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
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('vendor.orders.update', $order->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-3">Order Summary</h2>
                    <p class="mb-2"><strong class="font-medium">Order Date:</strong> {{ $order->created_at->format('M d, Y H:i A') }}</p>
                    <p class="mb-2"><strong class="font-medium">Customer:</strong> {{ $order->user->name ?? 'Guest User' }}</p>
                    <p class="mb-2"><strong class="font-medium">Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                    <p class="mb-2"><strong class="font-medium">Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                    <p class="mb-2"><strong class="font-medium">Shipping Address:</strong> {{ $order->shipping_address }}</p>

                    <h3 class="text-lg font-semibold text-gray-700 mt-4 mb-2">Items in this Order (from your store):</h3>
                    <ul class="list-disc list-inside mb-4">
                        @php
                            $vendorItems = $order->orderItems->filter(fn($item) => $item->product->vendor_id === Auth::id());
                        @endphp
                        @forelse($vendorItems as $item)
                            <li>{{ $item->product->name ?? 'Product N/A' }} ({{ $item->productVariant->variant_name ?? 'Base' }}) x {{ $item->quantity }} @ ${{ number_format($item->price, 2) }}</li>
                        @empty
                            <li>No products from your store in this order.</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-3">Update Order Status</h2>
                    <div class="mb-4">
                        <label for="order_status" class="block text-gray-700 text-sm font-bold mb-2">Order Status:</label>
                        <select name="order_status" id="order_status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('order_status') border-red-500 @enderror" required>
                            <option value="pending" {{ old('order_status', $order->order_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ old('order_status', $order->order_status) == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ old('order_status', $order->order_status) == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ old('order_status', $order->order_status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ old('order_status', $order->order_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('order_status')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- If you want vendors to manage payment status (uncomment and test carefully) --}}
                    {{-- <div class="mb-4">
                        <label for="payment_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Status:</label>
                        <select name="payment_status" id="payment_status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('payment_status') border-red-500 @enderror" required>
                            <option value="pending" {{ old('payment_status', $order->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('payment_status', $order->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ old('payment_status', $order->payment_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ old('payment_status', $order->payment_status) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        @error('payment_status')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div> --}}

                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Vendor Notes (Optional):</label>
                        <textarea name="notes" id="notes" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Order</button>
            </div>
        </form>
    </div>
@endsection