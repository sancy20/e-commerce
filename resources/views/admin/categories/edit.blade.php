@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Category: {{ $category->name }}</h1>
        <a href="{{ route('admin.categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Categories</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Category Name:</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="mb-4">
                <label for="parent_id" class="block text-gray-700 text-sm font-bold mb-2">Parent Category (Optional):</label>
                <select name="parent_id" id="parent_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="">None (This is a Main Category)</option>
                    @foreach ($mainCategories as $mainCategory)
                        <option value="{{ $mainCategory->id }}" {{ old('parent_id', $category->parent_id) == $mainCategory->id ? 'selected' : '' }}>
                            {{ $mainCategory->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center justify-end">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Update Category</button>
            </div>
        </form>
    </div>
@endsection