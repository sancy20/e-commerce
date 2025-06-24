@extends('layouts.vendor')

@section('title', 'Vendor Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Welcome to Your Vendor Dashboard!</h1>

    {{-- Tier Upgrade Reminder Message --}}
    @if (!$user->isDiamondVendor()) {{-- Check if NOT Diamond Tier --}}
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Upgrade Your Tier!</strong>
            <span class="block sm:inline">You are currently a {{ $user->vendor_tier }} vendor. Upgrade to Diamond tier to unlock all premium features and benefits!</span>
            <a href="{{ route('vendor.upgrade_request.form') }}" class="ml-2 inline-block text-yellow-900 underline hover:no-underline">Request Upgrade &rarr;</a>
        </div>
    @endif
    {{-- End Tier Upgrade Reminder --}}

    <!-- {{-- Stripe Connect Status Message --}}
    @if ($user->isVendor() && !$user->payouts_enabled)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Action Required:</strong>
            <span class="block sm:inline">Your Stripe account is not fully connected or payouts are not enabled. You will not receive payouts until this is resolved.</span>
            <a href="{{ route('vendor.stripe_connect.onboard') }}" class="ml-2 inline-block text-red-900 underline hover:no-underline">Connect/Complete Setup &rarr;</a>
        </div>
    @endif
    @if ($user->canReceivePayouts())
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Stripe Connected!</strong>
            <span class="block sm:inline">Your Stripe account is ready for payouts.</span>
        </div>
    @endif -->


    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Quick Stats (Example)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <p class="text-gray-600">Total Products</p>
                <p class="text-2xl font-bold text-blue-700 mt-2">{{ $totalProducts }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <p class="text-gray-600">Total Revenue</p>
                <p class="text-2xl font-bold text-green-700 mt-2">${{ number_format($totalVendorRevenue, 2) }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                <p class="text-gray-600">Total Orders</p>
                <p class="text-2xl font-bold text-yellow-700 mt-2">{{ $ordersContainingVendorProducts }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Your Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('vendor.products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center">Add New Product</a>
            <a href="{{ route('vendor.products.index') }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg text-center">Manage Your Products</a>
            <a href="{{ route('vendor.orders.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center">View My Orders</a>
            <a href="{{ route('vendor.reports.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg text-center">View My Reports</a>
        </div>
        {{-- Conditionally show Diamond-only feature example --}}
        @if ($user->isDiamondVendor())
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-center">
                <p class="font-bold">🎉 Diamond Tier Exclusive Feature! 🎉</p>
                <p class="text-sm">Access advanced marketing tools here.</p>
            </div>
        @endif
    </div>
@endsection