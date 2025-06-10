@extends('layouts.admin')

@section('title', 'Income Reporting Dashboard')

@section('content')
    <!-- <h1 class="text-2xl font-bold mb-6">Income Reporting Dashboard</h1> -->

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Date Range Filter Form --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Filter by Date Range</h2>
        <form action="{{ route('admin.reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="end_date" class="block text-gray-700 text-sm font-bold mb-2">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500 text-lg">Total Revenue (Paid Orders)</p>
            <h3 class="text-4xl font-bold text-green-600 mt-2">${{ number_format($totalRevenue, 2) }}</h3>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500 text-lg">Total Orders</p>
            <h3 class="text-4xl font-bold text-blue-600 mt-2">{{ $totalOrders }}</h3>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500 text-lg">Paid Orders Count</p>
            <h3 class="text-4xl font-bold text-purple-600 mt-2">{{ $paidOrdersCount }}</h3>
        </div>
    </div>

    {{-- Links to detailed reports --}}
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Detailed Reports</h2>
        <ul class="list-disc list-inside space-y-2">
            <li><a href="{{ route('admin.reports.sales-by-date', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="text-blue-600 hover:underline">Sales by Date</a></li>
            <li><a href="{{ route('admin.reports.product-sales', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="text-blue-600 hover:underline">Best Selling Products</a></li>
            <li><a href="{{ route('admin.reports.category-sales', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="text-blue-600 hover:underline">Best Selling Categories</a></li>
        </ul>
    </div>
@endsection