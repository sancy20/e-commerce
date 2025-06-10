@extends('layouts.admin')

@section('title', 'Inquiry Details: ' . $inquiry->subject)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Inquiry Details</h1>
        <a href="{{ route('admin.inquiries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Inquiries</a>
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

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Inquiry Information</h2>
                <p class="mb-2"><strong class="font-medium">From:</strong> {{ $inquiry->sender->name ?? 'Guest' }} ({{ $inquiry->sender->email ?? 'N/A' }})</p>
                <p class="mb-2"><strong class="font-medium">To:</strong> {{ $inquiry->recipient->name ?? 'Admin' }} ({{ $inquiry->recipient->email ?? 'N/A' }})</p>
                <p class="mb-2"><strong class="font-medium">Subject:</strong> {{ $inquiry->subject }}</p>
                <p class="mb-2"><strong class="font-medium">Product:</strong>
                    @if ($inquiry->product)
                        <a href="{{ route('products.show', $inquiry->product->slug) }}" target="_blank" class="text-blue-600 hover:underline">{{ $inquiry->product->name }}</a>
                    @else
                        N/A
                    @endif
                </p>
                <p class="mb-2"><strong class="font-medium">Source:</strong> {{ ucfirst($inquiry->source_type) }}</p>
                <p class="mb-2"><strong class="font-medium">Received:</strong> {{ $inquiry->created_at->format('M d, Y H:i A') }}</p>
                <p class="mb-2"><strong class="font-medium">Status:</strong>
                    @if ($inquiry->is_read)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Read</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Unread</span>
                    @endif
                </p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Message</h2>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $inquiry->message }}</p> {{-- Use whitespace-pre-wrap to preserve line breaks --}}
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            @unless($inquiry->is_read)
                <form action="{{ route('admin.inquiries.mark_as_read', $inquiry->id) }}" method="POST" onsubmit="return confirm('Mark this inquiry as read?');">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Mark as Read</button>
                </form>
            @endunless
            {{-- Add reply form here later --}}
            <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Inquiry</button>
            </form>
        </div>
    </div>
@endsection