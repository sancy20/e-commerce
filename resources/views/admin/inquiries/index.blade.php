@extends('layouts.admin')

@section('title', 'Customer Inquiries')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Customer Inquiries</h1>
    
    {{-- Main content wrapper with Flexbox --}}
    <div class="flex flex-col md:flex-row gap-8">

        <aside class="md:w-1/4 lg:w-1/5">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Filters</h3>
                <form action="{{ route('admin.inquiries.index') }}" method="GET">
                    <div class="mb-4">
                        <label for="is_read" class="block text-gray-700 text-sm font-bold mb-2">Read Status:</label>
                        <select name="is_read" id="is_read" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <option value="">All</option>
                            <option value="false" {{ request('is_read') === 'false' ? 'selected' : '' }}>Unread</option>
                            <option value="true" {{ request('is_read') === 'true' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="source_type" class="block text-gray-700 text-sm font-bold mb-2">Source Type:</label>
                        <select name="source_type" id="source_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                            <option value="">All</option>
                            <option value="general" {{ request('source_type') === 'general' ? 'selected' : '' }}>General Form</option>
                            <option value="email" {{ request('source_type') === 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Apply Filters</button>
                        <a href="{{ route('admin.inquiries.index') }}" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-center">Reset</a>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Main content area for the inquiries table --}}
        <main class="flex-1">
            <div class="bg-white shadow-md rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    {{-- Your existing table head --}}
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($inquiries as $inquiry)
                            <tr>
                                <td class="px-6 py-4">{{ $inquiry->id }}</td>
                                <td class="px-6 py-4">{{ $inquiry->sender->name ?? 'Guest' }}</td>
                                <td class="px-6 py-4 max-w-xs truncate" title="{{ $inquiry->subject }}">{{ \Illuminate\Support\Str::limit($inquiry->subject, 40) }}</td>
                                <td class="px-6 py-4">
                                    @if ($inquiry->is_read)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Read</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Unread</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $inquiry->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No inquiries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $inquiries->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection