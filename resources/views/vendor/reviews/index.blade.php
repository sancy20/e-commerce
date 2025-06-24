@extends('layouts.vendor')

@section('title', 'My Product Reviews')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <!-- <h1 class="text-2xl font-bold">Reviews for My Products</h1> -->
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reviews as $review)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('products.show', $review->product->slug ?? '#') }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $review->product->name ?? 'Product Deleted' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $review->user->name ?? 'User Deleted' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="text-yellow-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating) &#9733; @else &#9734; @endif
                                @endfor
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="{{ $review->comment }}">
                            {{ \Illuminate\Support\Str::limit($review->comment, 50, '...') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($review->is_approved)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('vendor.reviews.show', $review->id) }}" class="text-blue-600 hover:text-blue-900">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No reviews found for your products.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    </div>
@endsection