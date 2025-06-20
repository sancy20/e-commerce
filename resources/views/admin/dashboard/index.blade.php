@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard: Pending Tasks</h1>

    <!-- {{-- Success/Error Messages (from redirect after action) --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
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
    @endif -->

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Pending Vendor Applications Card --}}
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-blue-500">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Vendor Applications</h2>
            <p class="text-4xl font-bold text-blue-600 mb-4">{{ $pendingVendorApplicationsCount }}</p>
            <a href="{{ route('admin.users.index', ['vendor_status' => 'pending_vendor']) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Review Applications</a>
        </div>

        <!-- {{-- Pending Review Approvals Card --}}
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-yellow-500">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Pending Reviews</h2>
            <p class="text-4xl font-bold text-yellow-600 mb-4">{{ $pendingReviewApprovalsCount }}</p>
            <a href="{{ route('admin.reviews.index', ['is_approved' => 'false']) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-md">Approve Reviews</a>
        </div> -->

        {{-- Pending Tier Upgrade Requests Card --}}
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-purple-500">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Upgrade Requests</h2>
            <p class="text-4xl font-bold text-purple-600 mb-4">{{ $pendingUpgradeRequestsCount }}</p>
            <a href="{{ route('admin.users.index', ['upgrade_request_status' => 'pending_upgrade']) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md">Review Requests</a>
        </div>

        {{-- New Orders Card (Example) --}}
        <div class="bg-white rounded-lg shadow-md p-6 text-center border-l-4 border-green-500">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">New Paid Orders</h2>
            <p class="text-4xl font-bold text-green-600 mb-4">{{ $newOrdersCount }}</p>
            <a href="{{ route('admin.orders.index', ['order_status' => 'pending', 'payment_status' => 'paid']) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md">Process Orders</a>
        </div>

    </div>

    <div class="mt-8 border-t border-gray-200 pt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Links</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('admin.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-lg text-center">Manage Products</a>
            <a href="{{ route('admin.reports.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-lg text-center">View Reports</a>
        </div>
    </div>
@endsection