@extends('layouts.admin')

@section('title', 'Shipping Method Details')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Shipping Method: {{ $shippingMethod->name }}</h1>
        <a href="{{ route('admin.shipping-methods.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Methods</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">ID:</strong> {{ $shippingMethod->id }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Name:</strong> {{ $shippingMethod->name }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Cost:</strong> ${{ number_format($shippingMethod->cost, 2) }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Description:</strong> {{ $shippingMethod->description ?? 'N/A' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Active:</strong>
                @if ($shippingMethod->is_active)
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                @endif
            </p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Created At:</strong> {{ $shippingMethod->created_at->format('M d, Y H:i A') }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Updated At:</strong> {{ $shippingMethod->updated_at->format('M d, Y H:i A') }}</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('admin.shipping-methods.edit', $shippingMethod->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Edit</a>
            <form action="{{ route('admin.shipping-methods.destroy', $shippingMethod->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this method?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
            </form>
        </div>
    </div>
@endsection