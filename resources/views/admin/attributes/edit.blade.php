@extends('layouts.admin')
@section('title', 'Edit Attribute: ' . $attribute->name)
@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Attribute: {{ $attribute->name }}</h1>

    <form action="{{ route('admin.attributes.values.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
        @csrf
        <h2 class="text-xl font-semibold mb-2">Manage Values</h2>
        <p class="text-sm text-gray-600 mb-4">Update names and upload default images for attribute values (e.g., color swatches).</p>
        <div class="space-y-6">
            @foreach($attribute->values as $value)
                <div class="flex items-center p-4 border rounded-lg gap-4">
                    <div class="w-1/4">
                        <label class="block text-xs font-medium text-gray-500">Value Name</label>
                        <input type="text" name="values[{{ $value->id }}][value]" value="{{ $value->value }}" class="shadow w-full p-2 border rounded">
                    </div>
                    <div class="w-1/4">
                         <label class="block text-xs font-medium text-gray-500">Current Image</label>
                        @if($value->image)
                            <img src="{{ asset('storage/' . $value->image) }}" alt="{{ $value->value }}" class="h-16 w-16 object-cover rounded">
                        @else
                            <span class="text-gray-400 text-sm h-16 flex items-center">No Image</span>
                        @endif
                    </div>
                    <div class="w-2/4">
                        <label for="image-{{ $value->id }}" class="text-xs font-medium text-gray-500">Upload New Image</label>
                        <input type="file" name="images[{{ $value->id }}]" id="image-{{ $value->id }}" class="block w-full text-sm">
                        @if($value->image)
                            <label class="inline-flex items-center mt-2 text-xs">
                                <input type="checkbox" name="remove_images[]" value="{{ $value->id }}" class="form-checkbox mr-1"> Remove Current Image
                            </label>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-right mt-6">
            <button type="submit" class="bg-green-500 text-white font-bold py-2 px-4 rounded">Save All Changes</button>
        </div>
    </form>
@endsection