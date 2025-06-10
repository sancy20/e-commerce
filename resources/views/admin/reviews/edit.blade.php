@extends('layouts.admin')

@section('title', 'Edit Review')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Review (ID: {{ $review->id }})</h1>
        <a href="{{ route('admin.reviews.show', $review->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Details</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <p class="text-gray-700"><strong class="font-semibold">Product:</strong>
                    <a href="{{ route('products.show', $review->product->slug ?? '#') }}" target="_blank" class="text-blue-600 hover:underline">
                        {{ $review->product->name ?? 'Product Not Found' }}
                    </a>
                </p>
                <p class="text-gray-700"><strong class="font-semibold">Reviewer:</strong> {{ $review->user->name ?? 'User Not Found' }}</p>
            </div>

            <div class="mb-4">
                <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                <select name="rating" id="rating" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('rating') border-red-500 @enderror" required>
                    <option value="5" {{ old('rating', $review->rating) == 5 ? 'selected' : '' }}>5 Stars</option>
                    <option value="4" {{ old('rating', $review->rating) == 4 ? 'selected' : '' }}>4 Stars</option>
                    <option value="3" {{ old('rating', $review->rating) == 3 ? 'selected' : '' }}>3 Stars</option>
                    <option value="2" {{ old('rating', $review->rating) == 2 ? 'selected' : '' }}>2 Stars</option>
                    <option value="1" {{ old('rating', $review->rating) == 1 ? 'selected' : '' }}>1 Star</option>
                </select>
                @error('rating')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
                <textarea name="comment" id="comment" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('comment') border-red-500 @enderror">{{ old('comment', $review->comment) }}</textarea>
                @error('comment')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_approved" id="is_approved" class="mr-2 leading-tight" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                <label for="is_approved" class="text-gray-700 text-sm font-bold">Approve Review?</label>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Review</button>
            </div>
        </form>
    </div>
@endsection