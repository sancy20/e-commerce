@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Our Products</h1>

    {{-- Main content wrapper with Flexbox --}}
    <div class="flex flex-col md:flex-row gap-8">

        <aside class="md:w-1/4 lg:w-1/5">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                <form action="{{ route('products.index') }}" method="GET">
                    <h3 class="text-xl font-semibold mb-4">Filters</h3>
                    
                    {{-- Search, Category, Price, and Sort By --}}
                    <div class="space-y-4 border-b pb-4 mb-4">
                        <div>
                            <label for="search" class="block text-sm font-bold mb-2">Search</label>
                            <input type="text" name="search" id="search" placeholder="Product name..." value="{{ request('search') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 text-sm">
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-bold mb-2">Category</label>
                            <select name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 text-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Price Range:</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}" class="shadow w-full text-sm">
                                <span>-</span>
                                <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}" class="shadow w-full text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Manually added Vendor Tier Filters --}}
                    <div class="space-y-2 border-b pb-4 mb-4">
                        <label class="block text-sm font-bold mb-2">Vendor</label>
                        
                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="tiers[Silver]" value="1" class="form-checkbox h-4 w-4 text-blue-600"
                                {{ (is_array(request('tiers')) && array_key_exists('Silver', request('tiers'))) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Silver</span>
                        </label>

                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="tiers[Gold]" value="1" class="form-checkbox h-4 w-4 text-blue-600"
                                {{ (is_array(request('tiers')) && array_key_exists('Gold', request('tiers'))) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Gold</span>
                        </label>

                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="tiers[Diamond]" value="1" class="form-checkbox h-4 w-4 text-blue-600"
                                {{ (is_array(request('tiers')) && array_key_exists('Diamond', request('tiers'))) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Diamond</span>
                        </label>
                    </div>
                    <div class="space-y-2 border-b pb-4 mb-4">
                        <label class="block text-sm font-bold mb-2">Rating</label>
                        @php
                            $ratings = [
                                ['value' => 5, 'label' => '5.0'],
                                ['value' => 4.5, 'label' => '4.5 & up'],
                                ['value' => 4, 'label' => '4.0 & up'],
                            ];
                        @endphp
                        @foreach ($ratings as $rating)
                            <label class="flex items-center text-sm">
                                <input type="radio" name="rating" value="{{ $rating['value'] }}" class="form-radio h-4 w-4 text-blue-600"
                                    {{ request('rating') == $rating['value'] ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-600">{{ $rating['label'] }}</span>
                            </label>
                        @endforeach
                        <a href="{{ route('products.index', array_merge(request()->except('page', 'rating'))) }}" class="text-xs text-blue-500 hover:underline mt-1 inline-block">Clear rating</a>
                    </div>
                    
                    <div class="flex flex-col space-y-2">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Apply Filters</button>
                        <a href="{{ route('products.index') }}" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-center">Reset</a>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Main content area for the product grid --}}
        <main class="flex-1">
        {{-- Product Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse ($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col transform transition duration-300 hover:scale-105">
                <a href="{{ route('products.show', $product->slug) }}">
                <img src="{{ $product->cover_image_url }}" 
                    alt="{{ $product->name }}" 
                    class="w-full h-48 object-cover">
                </a>
                <div class="p-4 flex flex-col flex-grow">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-xl font-semibold text-gray-800 hover:text-blue-600">{{ $product->name }}</a> 
                    <div class="flex justify-between items-center text-xs text-gray-500 mt-2">
                        <div class="flex items-center">
                            @php
                                $averageRating = $product->averageRating() ?? 0;
                                $reviewCount = $product->approvedReviews->count();
                            @endphp
                            @if ($reviewCount > 0)
                                <span class="text-yellow-500 mr-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= round($averageRating) ? '★' : '☆' }}</span>
                                    @endfor
                                </span>
                                <span class="text-gray-700">({{ number_format($averageRating, 1) }})</span>
                            @else
                                <span class="text-gray-400">No reviews</span>
                            @endif
                        </div>
                        <span class="font-semibold">{{ (int)($product->sold_count ?? 0) }} Sold</span>
                    </div>

                    <p class="text-gray-900 font-bold text-lg mt-2">{{ $product->price_range }}</p>

                    @if($product->vendor)
                    <div class="flex items-center space-x-2 text-xs text-gray-600 mt-2">
                        <span>Vendor: <span class="font-semibold">{{ $product->vendor->name }}</span></span>
                        @if($product->vendor->vendor_tier)
                            <span class="px-2 py-0.5 font-bold leading-none text-yellow-800 bg-yellow-200 rounded-full">
                                {{ $product->vendor->vendor_tier }}
                            </span>
                        @endif
                    </div>
                @endif

                    <div class="flex-grow"></div>

                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('products.show', $product->slug) }}" class="w-full text-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-xs">
                            View Options
                        </a>
                        @if ($product->vendor)
                            <a href="{{ route('inquiries.create', $product->id) }}" class="w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md text-xs">
                                Contact Seller
                            </a>
                        @endif
                    </div>
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