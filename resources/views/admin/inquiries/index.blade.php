@extends('layouts.admin')

@section('title', 'Customer Inquiries')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <!-- <h1 class="text-2xl font-bold">Customer Inquiries</h1> -->
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DEBUGGING AID: Last Sent Email Display --}}
    @if (isset($lastEmailSent) && $lastEmailSent)
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Last Email Sent (Debug):</strong>
            <span class="block sm:inline">
                To: {{ implode(', ', array_keys($lastEmailSent['to'])) }}<br>
                Subject: {{ $lastEmailSent['subject'] }}<br>
                Sent At: {{ $lastEmailSent['sent_at'] }}
            </span>
            <span class="absolute top-0 right-0 px-2 py-1 text-xs font-semibold text-blue-700">Expires in 5 min</span>
        </div>
    @else
        <div class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-3 rounded relative mb-4" role="alert">
            No email sent yet (or debug info expired). Send an inquiry to test.
        </div>
    @endif
    {{-- END DEBUGGING AID --}}

    {{-- Filters --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Filter Inquiries</h2>
        <form action="{{ route('admin.inquiries.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="is_read" class="block text-gray-700 text-sm font-bold mb-2">Read Status:</label>
                <select name="is_read" id="is_read" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="">All</option>
                    <option value="false" {{ request('is_read') === 'false' ? 'selected' : '' }}>Unread</option>
                    <option value="true" {{ request('is_read') === 'true' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            <div>
                <label for="source_type" class="block text-gray-700 text-sm font-bold mb-2">Source Type:</label>
                <select name="source_type" id="source_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    <option value="">All</option>
                    <option value="general" {{ request('source_type') === 'general' ? 'selected' : '' }}>General Form</option>
                    <option value="email" {{ request('source_type') === 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Apply Filters</button>
                <a href="{{ route('admin.inquiries.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2">Reset</a>
            </div>
        </form>
    </div>


    <div class="bg-white shadow-md rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inquiry->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inquiry->sender->name ?? 'Guest' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap max-w-xs truncate" title="{{ $inquiry->subject }}">
                            {{ \Illuminate\Support\Str::limit($inquiry->subject, 50, '...') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($inquiry->product)
                                <a href="{{ route('products.show', $inquiry->product->slug) }}" target="_blank" class="text-blue-600 hover:underline">{{ $inquiry->product->name }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $inquiry->recipient->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($inquiry->source_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($inquiry->is_read)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Read</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Unread</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inquiry->created_at->format('M d, Y H:i A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">No inquiries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $inquiries->links() }}
        </div>
    </div>
@endsection