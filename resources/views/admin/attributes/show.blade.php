@extends('layouts.admin')

@section('title', 'Attribute Details: ' . $attribute->name)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Attribute Details: {{ $attribute->name }}</h1>
        <a href="{{ route('admin.attributes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Attributes</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">ID:</strong> {{ $attribute->id }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Name:</strong> {{ $attribute->name }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700"><strong class="font-semibold">Slug:</strong> {{ $attribute->slug }}</p>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Values for {{ $attribute->name }}</h2>
        <a href="{{ route('admin.attribute-values.create', ['attribute_id' => $attribute->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add New Value</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attribute->values as $value)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $value->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $value->value }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $value->slug }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.attribute-values.edit', $value->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('admin.attribute-values.destroy', $value->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this attribute value?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No values defined for this attribute.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection