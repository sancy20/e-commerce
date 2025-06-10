@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome, {{ $user->name }}!</h1>

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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
            <nav class="space-y-2">
                <a href="{{ route('dashboard.index') }}" class="block px-4 py-2 text-lg font-medium text-blue-700 bg-blue-50 rounded-md">Dashboard Overview</a>
                <a href="{{ route('dashboard.orders') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Orders</a>
                <a href="{{ route('dashboard.profile') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Profile</a>
                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Wishlist</a> {{-- ADD THIS LINE --}}
                {{-- Add more links here (e.g., Addresses, Payment Methods) --}}
            </nav>
        </div>

        {{-- Main Dashboard Content --}}
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Dashboard Overview</h2>

            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">Recent Orders</h3>
                @forelse ($recentOrders as $order)
                    <div class="flex items-center justify-between border-b border-gray-200 py-3 last:border-b-0">
                        <div>
                            <a href="{{ route('dashboard.orders.show', $order->id) }}" class="text-blue-600 hover:underline font-medium">Order #{{ $order->order_number }}</a>
                            <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-lg">${{ number_format($order->total_amount, 2) }}</span>
                            <p class="text-sm text-gray-600">{{ ucfirst($order->order_status) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">You haven't placed any orders yet.</p>
                @endforelse
                @if ($recentOrders->count() > 0)
                    <div class="mt-4 text-right">
                        <a href="{{ route('dashboard.orders') }}" class="text-blue-600 hover:underline">View All Orders &rarr;</a>
                    </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('products.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center">Continue Shopping</a>
                    <a href="{{ route('dashboard.profile') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg text-center">Update Profile</a>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection