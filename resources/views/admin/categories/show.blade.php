@extends('layouts.admin')

@section('title', 'Category Details')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Category Details</h1>
        <a href="{{ route('admin.categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Categories</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">ID:</strong> {{ $category->id }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Name:</strong> {{ $category->name }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Slug:</strong> {{ $category->slug }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Created At:</strong> {{ $category->created_at->format('M d, Y H:i A') }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Updated At:</strong> {{ $category->updated_at->format('M d, Y H:i A') }}</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('admin.categories.edit', $category->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded mr-2">Edit</a>
            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
            </form>
        </div>
    </div>
@endsection