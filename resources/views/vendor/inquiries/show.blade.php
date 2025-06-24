@extends('layouts.vendor')

@section('title', 'Inquiry Details: ' . $inquiry->subject)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <!-- <h1 class="text-2xl font-bold">Inquiry Details</h1> -->
        <a href="{{ route('vendor.inquiries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to My Inquiries</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Customer Inquiry Information</h2>
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
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Customer Message</h2>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $inquiry->message }}</p>

                <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Your Reply</h2>

                @if ($inquiry->vendor_reply)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <p class="font-semibold text-gray-700 mb-2">Your current reply:</p>
                        <p class="text-gray-800">{{ $inquiry->vendor_reply }}</p>
                        <p class="text-sm text-gray-500 mt-2">Replied on: {{ $inquiry->replied_at->format('M d, Y H:i A') }}</p>
                    </div>
                @else
                    <p class="text-gray-600 mb-4">You have not replied to this inquiry yet.</p>
                @endif

                <form action="{{ route('vendor.inquiries.update', $inquiry->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="vendor_reply" class="block text-gray-700 text-sm font-bold mb-2">Reply Message:</label>
                        <textarea name="vendor_reply" id="vendor_reply" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('vendor_reply') border-red-500 @enderror" required>{{ old('vendor_reply', $inquiry->vendor_reply) }}</textarea>
                        @error('vendor_reply')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ $inquiry->vendor_reply ? 'Update Reply' : 'Submit Reply' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection