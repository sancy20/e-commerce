@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Our Products</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            {{-- Search Bar --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Search Products:</label>
                <input type="text" name="search" id="search" placeholder="Search by name, description, SKU..."
                       value="{{ request('search') }}"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            {{-- Category Filter --}}
            <div>
                <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                <select name="category" id="category"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Price Range Filter --}}
            <div class="md:col-span-4 grid grid-cols-2 gap-4">
                <div>
                    <label for="min_price" class="block text-gray-700 text-sm font-bold mb-2">Min Price:</label>
                    <input type="number" name="min_price" id="min_price" step="0.01" placeholder="e.g., 10.00"
                           value="{{ request('min_price') }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label for="max_price" class="block text-gray-700 text-sm font-bold mb-2">Max Price:</label>
                    <input type="number" name="max_price" id="max_price" step="0.01" placeholder="e.g., 100.00"
                           value="{{ request('max_price') }}"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            {{-- Sorting Options (Optional) --}}
            <div class="md:col-span-2">
                <label for="sort" class="block text-gray-700 text-sm font-bold mb-2">Sort By:</label>
                <select name="sort" id="sort"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z-A</option>
                </select>
            </div>


            {{-- Submit and Reset Buttons --}}
            <div class="md:col-span-2 flex space-x-2">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Apply Filters
                </button>
                <a href="{{ route('products.index') }}" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-300 hover:scale-105">
                <a href="{{ route('products.show', $product->slug) }}">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">No Image</div>
                    @endif
                </a>
                <div class="p-4">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-xl font-semibold text-gray-800 hover:text-blue-600">{{ $product->name }}</a>
                    <p class="text-gray-600 text-sm mt-1">{{ $product->category->name ?? 'Uncategorized' }}</p>

                    {{-- Average Rating Display --}}
                    @php
                        $averageRating = number_format($product->averageRating(), 1);
                        $reviewCount = $product->approvedReviews->count();
                    @endphp
                    @if ($reviewCount > 0)
                        <div class="flex items-center mt-1">
                            <span class="text-yellow-500 text-md mr-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($averageRating)) &#9733; @else &#9734; @endif
                                @endfor
                            </span>
                            <span class="text-gray-700 text-xs">({{ $averageRating }})</span>
                        </div>
                    @else
                        <p class="text-gray-500 text-xs mt-1">No reviews</p>
                    @endif
                    {{-- End Average Rating --}}

                    <p class="text-gray-900 font-bold text-lg mt-2">${{ number_format($product->effective_price, 2) }}</p>

                    <form action="{{ route('cart.add') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        {{-- Note: For products with variants, this 'Add to Cart' button needs variant selection on product.show --}}
                        {{-- Here, it adds the base product. For variant selection, use product.show --}}
                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->effective_stock_quantity }}"
                            class="form-input w-20 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm">
                            Add to Cart
                        </button>
                    </form>
                    {{-- Add to Wishlist Button --}}
                    @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-3 rounded text-sm">
                                Add to Wishlist
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-600">No products found matching your criteria.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $products->appends(request()->except('page'))->links() }}
    </div>
</div>
@endsection