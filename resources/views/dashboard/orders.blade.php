@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Orders</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
            <nav class="space-y-2">
                <a href="{{ route('dashboard.index') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">Dashboard Overview</a>
                <a href="{{ route('dashboard.orders') }}" class="block px-4 py-2 text-lg font-medium text-blue-700 bg-blue-50 rounded-md">My Orders</a>
                <a href="{{ route('dashboard.profile') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Profile</a>
                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Wishlist</a> 

                @if(auth()->user() && auth()->user()->isVendor())
                    <div class="border-t pt-2 mt-2">
                        <span class="block px-4 pt-2 text-xs font-semibold text-gray-500 uppercase">Vendor Panel</span>
                        <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-md">
                            Go to Vendor Dashboard
                        </a>
                    </div>
                @endif

                <div class="border-t pt-2 mt-2">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-lg text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-md">
                            Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        {{-- Order List Content --}}
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">All My Orders</h2>

            @forelse ($orders as $order)
                <div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xl font-semibold">Order #{{ $order->order_number }}</h3>
                        <span class="text-lg font-bold">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-2">Placed on {{ $order->created_at->format('M d, Y H:i A') }}</p>
                    <p class="text-gray-700 mb-4">Status: <span class="font-medium">{{ ucfirst($order->order_status) }}</span></p>
                    <a href="{{ route('dashboard.orders.show', $order->id) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold py-2 px-4 rounded-md">View Details</a>
                </div>
            @empty
                <p class="text-gray-600">You have no past orders.</p>
            @endforelse

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection