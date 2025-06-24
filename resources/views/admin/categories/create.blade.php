@extends('layouts.admin')

@section('title', 'Create Category')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Create New Category</h1>
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
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            {{-- Category Name & Parent --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold mb-2">Category Name:</label>
                    <input type="text" name="name" id="name" class="shadow w-full p-2 border rounded" value="{{ old('name', $category->name) }}" required>
                    @error('name') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-bold mb-2">Parent Category:</label>
                    <select name="parent_id" id="parent_id" class="shadow w-full p-2 border rounded">
                        <option value="">None (Main Category)</option>
                        @foreach ($categories as $mainCategory)
                            <option value="{{ $mainCategory->id }}" {{ old('parent_id', $category->parent_id) == $mainCategory->id ? 'selected' : '' }}>
                                {{ $mainCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 border-t pt-6">
                <h3 class="text-lg font-semibold mb-2">Associated Attributes</h3>
                <p class="text-gray-600 text-xs mb-4">Select the attributes that should be available for products in this category.</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach ($attributes as $attribute)
                        <div>
                            <label for="attribute-{{ $attribute->id }}" class="flex items-center p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="attributes[]" id="attribute-{{ $attribute->id }}" value="{{ $attribute->id }}" class="form-checkbox h-5 w-5 text-blue-600"
                                    {{-- Check if this attribute is already linked to the category, or if it was checked on a failed validation attempt --}}
                                    @if(is_array(old('attributes')) && in_array($attribute->id, old('attributes')))
                                        checked
                                    @elseif(!old('attributes') && $category->attributes->contains($attribute->id))
                                        checked
                                    @endif
                                >
                                <span class="ml-3 text-gray-700 font-medium">{{ $attribute->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('attributes') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center mt-4 justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Create Category</button>
            </div>
        </form>
    </div>
@endsection