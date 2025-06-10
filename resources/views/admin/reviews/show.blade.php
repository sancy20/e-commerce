@extends('layouts.admin')

@section('title', 'Review Details')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Review Details (ID: {{ $review->id }})</h1>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Reviews</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Review Information</h2>
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
                <p class="mb-2"><strong class="font-medium">Approved:</strong>
                    @if ($review->is_approved)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                    @endif
                </p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Review Comment</h2>
                <p class="text-gray-700">{{ $review->comment ?? 'No comment provided.' }}</p>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.reviews.edit', $review->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Edit Review</a>
            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete Review</button>
            </form>
        </div>
    </div>
@endsection