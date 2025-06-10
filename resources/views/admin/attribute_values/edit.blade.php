@extends('layouts.admin')

@section('title', 'Edit Attribute Value')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Value: {{ $attributeValue->value }}</h1>
        <a href="{{ route('admin.attribute-values.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Values</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.attribute-values.update', $attributeValue->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="attribute_id" class="block text-gray-700 text-sm font-bold mb-2">Select Attribute:</label>
                <select name="attribute_id" id="attribute_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('attribute_id') border-red-500 @enderror" required>
                    <option value="">-- Select an Attribute --</option>
                    @foreach ($attributes as $attribute)
                        <option value="{{ $attribute->id }}" {{ old('attribute_id', $attributeValue->attribute_id) == $attribute->id ? 'selected' : '' }}>
                            {{ $attribute->name }}
                        </option>
                    @endforeach
                </select>
                @error('attribute_id')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="value" class="block text-gray-700 text-sm font-bold mb-2">Value:</label>
                <input type="text" name="value" id="value" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('value') border-red-500 @enderror" value="{{ old('value', $attributeValue->value) }}" required>
                @error('value')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-end">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Value</button>
            </div>
        </form>
    </div>
@endsection