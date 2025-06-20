@extends('layouts.app')

@section('title', $product->name)

@section('content')
@php
    // Prepare data for the JavaScript component
    $productVariantsJson = Js::from($product->variants->load('attributeValues.attribute'));
    $baseProductJson = Js::from($product->load('attributeValues.attribute'));

    // This logic dynamically finds the primary attribute (like Color) for each variant image
    $galleryImages = collect();
    if ($product->image) {
        $galleryImages->push(['url' => $product->image, 'attribute_name' => null, 'value_id' => null]);
    }
    foreach ($product->variants as $variant) {
        if ($variant->image && !$galleryImages->contains('url', '==', $variant->image)) {
            $primaryValue = $variant->attributeValues->sortBy('attribute.name')->first();
            $galleryImages->push([
                'url' => $variant->image,
                'attribute_name' => $primaryValue ? $primaryValue->attribute->name : null,
                'value_id' => $primaryValue ? $primaryValue->id : null,
            ]);
        }
    }
@endphp

<div class="container mx-auto px-4 py-8">
    <div
        x-data="productViewComponent({
            variants: {{ $productVariantsJson }},
            baseProduct: {{ $baseProductJson }}
        })"
        x-init="init()"
        class="bg-white rounded-lg shadow-md p-6 lg:flex lg:space-x-8"
    >
        {{-- Image Section --}}
        <div class="lg:w-1/2">
            <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-96 object-contain rounded-lg mb-4">
            
            @if ($galleryImages->count() > 1)
                <div class="flex flex-wrap gap-2">
                    @foreach ($galleryImages as $image)
                        <img src="{{ asset('storage/' . $image['url']) }}"
                             @click="selectAttributeByThumbnail('{{ $image['attribute_name'] }}', {{ $image['value_id'] ?? 'null' }}, '{{ asset('storage/' . $image['url']) }}')"
                             alt="Product Thumbnail"
                             class="w-16 h-16 object-cover rounded-md cursor-pointer border-2 transition-all"
                             :class="{ 'border-blue-500': activeImage === '{{ asset('storage/' . $image['url']) }}', 'border-transparent': activeImage !== '{{ asset('storage/' . $image['url']) }}' }">
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Details Section --}}
        <div class="lg:w-1/2 mt-6 lg:mt-0">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
            <p class="text-gray-600 text-lg mb-2">{{ $product->category->name ?? 'Uncategorized' }}</p>
            
            <div class="flex items-center mb-4">
                 @if ($product->approvedReviews->count() > 0)
                    <span class="text-yellow-500 text-xl mr-2">
                        @for ($i = 1; $i < 6; $i++)
                            <span>{{ $i <= $product->averageRating() ? '★' : '☆' }}</span>
                        @endfor
                    </span>
                    <span class="text-gray-700 text-sm">({{ number_format($product->averageRating(), 1) }} / 5 from {{ $product->approvedReviews->count() }} reviews)</span>
                @else
                    <span class="text-gray-500 text-sm">No reviews yet.</span>
                @endif
            </div>

            <p class="text-gray-900 font-bold text-3xl my-6"><span x-text="displayPrice"></span></p>

            {{-- Display attributes for simple products as text --}}
            <div class="mb-4" x-show="productVariants.length === 0 && baseProduct.attribute_values.length > 0">
                <template x-for="av in baseProduct.attribute_values" :key="av.id">
                    <div class="flex items-center text-sm text-gray-700">
                        <strong class="font-semibold mr-2" x-text="av.attribute.name + ':'"></strong>
                        <span x-text="av.value"></span>
                    </div>
                </template>
            </div>

            <p class="text-gray-700 leading-relaxed mb-6">{{ $product->description }}</p>

            <div class="flex items-center text-gray-700 mb-4"><span class="font-semibold mr-2">SKU:</span> <span x-text="displaySku"></span></div>
            <div class="flex items-center text-gray-700 mb-6"><span class="font-semibold mr-2">Availability:</span> <span x-html="displayStock"></span></div>

            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" :value="selectedVariant ? selectedVariant.id : ''">

                {{-- Variant selectors only show if variants exist --}}
                <template x-if="attributes.length > 0">
                    <div class="mb-4">
                        <template x-for="attribute in attributes" :key="attribute.id">
                            <div class="mb-2">
                                <label :for="'attribute-' + attribute.id" class="block text-sm font-bold text-gray-700 mb-2" x-text="attribute.name + ':'"></label>
                                <select :id="'attribute-' + attribute.id" x-model="selection[attribute.name]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">Select <span x-text="attribute.name"></span></option>
                                    <template x-for="value in attribute.values" :key="value.id">
                                        <option :value="value.id" :disabled="!isOptionAvailable(attribute.name, value.id)" x-text="value.value"></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                    </div>
                </template>
                
                <p x-show="productVariants.length > 0 && !isSelectionComplete" class="text-red-500 text-sm mb-4">Please select all options.</p>

                <div class="flex items-center space-x-2 mt-4">
                    <div class="flex space-x-2 items-center">
                        <input type="number" name="quantity" value="1" min="1" :max="availableStock" class="form-input w-24 text-center border-gray-300 rounded-md shadow-sm" :disabled="availableStock === 0">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg" :disabled="(productVariants.length > 0 && !isSelectionComplete) || availableStock === 0">
                            <span x-text="addToCartText"></span>
                        </button>
                        @auth
                        <form action="{{ route('wishlist.add') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="product_variant_id" :value="selectedVariant ? selectedVariant.id : ''">
                            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-lg" :disabled="productVariants.length > 0 && !isSelectionComplete">
                                Add to Wishlist
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </form>

             <div class="mt-8 flex justify-between items-center">
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">← Back to Products</a>
                @if ($product->vendor)
                    <a href="{{ route('inquiries.create', $product->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                        Contact Seller
                    </a>
                @endif
            </div>
        </div>
    </div>

{{-- This is the original Reviews Section, now included in the final code --}}
    <div class="mt-12 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Reviews</h2>
        @auth
            @if (!$product->reviews()->where('user_id', Auth::id())->exists())
                <div class="mb-8 p-6 border border-gray-200 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Submit Your Review</h3>
                    <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                            <select name="rating" id="rating" class="shadow w-full" required>
                                <option value="">Select a rating</option>
                                <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5 Stars)</option>
                                <option value="4">&#9733;&#9733;&#9733;&#9733; (4 Stars)</option>
                                <option value="3">&#9733;&#9733;&#9733; (3 Stars)</option>
                                <option value="2">&#9733;&#9733; (2 Stars)</option>
                                <option value="1">&#9733; (1 Star)</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment (Optional):</label>
                            <textarea name="comment" id="comment" rows="4" class="shadow w-full">{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Submit Review
                        </button>
                    </form>
                </div>
            @else
                <p class="text-md text-gray-600 mb-4">You have already submitted a review for this product. Thank you!</p>
            @endif
        @else
            <p class="text-md text-gray-600">Please <a href="{{ route('login') }}" class="text-blue-500 hover:underline">log in</a> to submit a review.</p>
        @endauth

        @if ($product->approvedReviews->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-xl font-semibold mb-4">What Customers Are Saying:</h3>
                @foreach ($product->approvedReviews->sortByDesc('created_at') as $review)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-center mb-2">
                            <p class="font-semibold mr-2">{{ $review->user->name ?? 'Anonymous User' }}</p>
                            <span class="text-yellow-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                @endfor
                            </span>
                            <span class="text-gray-500 text-sm ml-auto">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="text-gray-700 mb-2">{{ $review->comment }}</p>
                        @endif
                        @if ($review->vendor_reply && $review->product->vendor)
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="font-semibold text-blue-800 mb-1">Reply from {{ $review->product->vendor->business_name ?? $review->product->vendor->name }}:</p>
                                <p class="text-blue-700 text-sm">{{ $review->vendor_reply }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-md text-gray-600">Be the first to review this product!</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function productViewComponent(data) {
    return {
        // --- DATA ---
        productVariants: data.variants,
        baseProduct: data.baseProduct,
        activeImage: data.baseProduct.image ? `{{ asset('storage') }}/${data.baseProduct.image}` : '',
        attributes: [], 
        selection: {}, 
        selectedVariant: null,

        // --- GETTERS (Computed Properties) ---
        get displayPrice() {
            const price = this.selectedVariant ? this.selectedVariant.price : this.baseProduct.price;
            return '$' + parseFloat(price).toFixed(2);
        },
        get displaySku() {
            return this.selectedVariant ? this.selectedVariant.sku : this.baseProduct.sku;
        },
        get availableStock() {
            if (this.productVariants.length > 0 && !this.isSelectionComplete) return 0;
            return this.selectedVariant ? parseInt(this.selectedVariant.stock_quantity) : parseInt(this.baseProduct.stock_quantity);
        },
        get displayStock() {
            if (this.productVariants.length > 0 && !this.isSelectionComplete) return `<span class="text-blue-600">Please select options</span>`;
            const stock = this.availableStock;
            if (stock > 0) return `<span class="text-green-600">${stock} in stock</span>` + (stock < 5 ? ` <span class="text-orange-500 text-sm">(Low stock!)</span>` : '');
            return `<span class="text-red-600">Out of Stock</span>`;
        },
        get isSelectionComplete() {
            if (this.attributes.length === 0) return true;
            return Object.values(this.selection).every(val => val);
        },
        get addToCartText() {
            if (this.productVariants.length > 0 && !this.isSelectionComplete) return 'Select Options';
            return this.availableStock > 0 ? 'Add to Cart' : 'Out of Stock';
        },
        
        // --- METHODS ---
        init() {
            if (this.productVariants.length > 0) {
                this.processAttributes();
                this.$watch('selection', () => this.updateSelectedVariant());
            }
        },
        processAttributes() {
            const attributesMap = {};
            this.productVariants.forEach(variant => {
                variant.attribute_values.forEach(av => {
                    const attr = av.attribute;
                    if (!attributesMap[attr.id]) {
                        attributesMap[attr.id] = { id: attr.id, name: attr.name, values: {} };
                        this.selection[attr.name] = '';
                    }
                    if (!attributesMap[attr.id].values[av.id]) {
                        attributesMap[attr.id].values[av.id] = { id: av.id, value: av.value };
                    }
                });
            });
            this.attributes = Object.values(attributesMap);
        },
        updateSelectedVariant() {
            this.selectedVariant = null;
            if (this.isSelectionComplete) {
                this.selectedVariant = this.productVariants.find(variant => {
                    return Object.values(this.selection).every(selectedValueId => {
                        const numericValueId = parseInt(selectedValueId);
                        return variant.attribute_values.some(av => av.id === numericValueId);
                    });
                }) || null;
            }
            
            if (this.selectedVariant && this.selectedVariant.image) {
                this.activeImage = `{{ asset('storage') }}/${this.selectedVariant.image}`;
            }
        },
        isOptionAvailable(attributeName, valueId) {
            let potentialVariants = this.productVariants;
            for (const key in this.selection) {
                const selectedValueId = this.selection[key] ? parseInt(this.selection[key]) : null;
                if (selectedValueId && key !== attributeName) {
                    potentialVariants = potentialVariants.filter(v => v.attribute_values.some(av => av.id === selectedValueId));
                }
            }
            return potentialVariants.some(v => v.attribute_values.some(av => av.id === valueId));
        },
        selectAttributeByThumbnail(attributeName, valueId, imageUrl) {
            this.activeImage = imageUrl;
            if (attributeName && valueId !== null) {
                this.selection[attributeName] = valueId;
            }
        }
    }
}
</script>
@endpush