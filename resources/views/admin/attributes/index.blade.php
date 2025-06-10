@extends('layouts.admin')

@section('title', 'Product Attributes')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <!-- <h1 class="text-2xl font-bold">Product Attributes</h1> -->
        <a href="{{ route('admin.attributes.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Attribute</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($attributes as $attribute)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $attribute->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $attribute->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $attribute->slug }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.attributes.show', $attribute->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View Values</a>
                            <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('admin.attributes.destroy', $attribute->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this attribute and all its values?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No attributes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $attributes->links() }}
        </div>
    </div>
@endsection