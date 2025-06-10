@extends('layouts.admin')

@section('title', 'Best Selling Products Report')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <!-- <h1 class="text-2xl font-bold">Best Selling Products Report</h1> -->
        <a href="{{ route('admin.reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Dashboard</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Date Range: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($productSales as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->product_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->product_sku ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $product->total_quantity_sold }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${{ number_format($product->total_revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No product sales data for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $productSales->links() }}
        </div>
    </div>
@endsection