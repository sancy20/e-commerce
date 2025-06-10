@extends('layouts.vendor')

@section('title', 'Review Details')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Review Details (ID: {{ $review->id }})</h1>
        <a href="{{ route('vendor.reviews.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to My Reviews</a>
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

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Customer Review Information</h2>
                <p class="mb-2"><strong class="font-medium">Product:</strong>
                    <a href="{{ route('products.show', $review->product->slug ?? '#') }}" target="_blank" class="text-blue-600 hover:underline">
                        {{ $review->product->name ?? 'Product Not Found' }}
                    </a>
                </p>
                <p class="mb-2"><strong class="font-medium">Reviewer:</strong> {{ $review->user->name ?? 'User Not Found' }}</p>
                <p class="mb-2"><strong class="font-medium">Review Date:</strong> {{ $review->created_at->format('M d, Y H:i A') }}</p>
                <p class="mb-2"><strong class="font-medium">Rating:</strong>
                    <span class="text-yellow-500">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $review->rating) &#9733; @else &#9734; @endif
                        @endfor
                    </span> ({{ $review->rating }} / 5)
                </p>
                <p class="mb-2"><strong class="font-medium">Status:</strong>
                    @if ($review->is_approved)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending Approval</span>
                    @endif
                </p>
                <h3 class="text-xl font-semibold text-gray-800 mt-4 mb-2">Customer Comment:</h3>
                <p class="text-gray-700">{{ $review->comment ?? 'No comment provided.' }}</p>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Your Reply to This Review</h2>

                @if ($review->vendor_reply)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <p class="font-semibold text-gray-700 mb-2">Your current reply:</p>
                        <p class="text-gray-800">{{ $review->vendor_reply }}</p>
                        <p class="text-sm text-gray-500 mt-2">Replied on: {{ $review->replied_at->format('M d, Y H:i A') }}</p>
                    </div>
                @else
                    <p class="text-gray-600 mb-4">You have not replied to this review yet.</p>
                @endif

                @if (!$review->is_approved)
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Cannot Reply:</strong>
                        <span class="block sm:inline">This review is currently awaiting admin approval and cannot be replied to.</span>
                    </div>
                @else
                    <form action="{{ route('vendor.reviews.update', $review->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="vendor_reply" class="block text-gray-700 text-sm font-bold mb-2">Your Reply:</label>
                            <textarea name="vendor_reply" id="vendor_reply" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('vendor_reply') border-red-500 @enderror" required>{{ old('vendor_reply', $review->vendor_reply) }}</textarea>
                            @error('vendor_reply')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ $review->vendor_reply ? 'Update Reply' : 'Submit Reply' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection