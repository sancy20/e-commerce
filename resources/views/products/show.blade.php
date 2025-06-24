@extends('layouts.app')

@section('title', $product->name)
@section('content')
@php
    $product_json = Js::from($product->load(['variants.attributeValues.attribute', 'images']));
    
    $attribute_images_json = Js::from(
        $product->variants
            ->flatMap->attributeValues
            ->whereNotNull('image')
            ->unique('id')
            ->pluck('image', 'id')
    );
    
    $variant_images_json = Js::from(
        $product->variants->whereNotNull('image')->pluck('image', 'id')
    );
    
    $initial_price_display = $product->price_range;

    $swatchImages = collect();
    $colorAttributeValues = $product->variants->flatMap->attributeValues->where('attribute.name', 'Color')->unique('id');

    foreach ($colorAttributeValues as $colorValue) {
        $variantWithImage = $product->variants->first(function ($variant) use ($colorValue) {
            return $variant->image && $variant->attributeValues->contains('id', $colorValue->id);
        });
        if ($variantWithImage) {
            $swatchImages[$colorValue->id] = asset('storage/' . $variantWithImage->image);
        } 
        elseif ($colorValue->image) {
            $swatchImages[$colorValue->id] = asset('storage/' . $colorValue->image);
        }
    }
    $swatch_images_json = Js::from($swatchImages);

    $initial_image_url = '';
    if ($product->image) {
        $initial_image_url = asset('storage/' . $product->image);
    } elseif ($product->images->isNotEmpty()) {
        $initial_image_url = asset('storage/' . $product->images->first()->path);
    } elseif ($firstVariantWithImage = $product->variants->firstWhere('image')) {
        $initial_image_url = asset('storage/' . $firstVariantWithImage->image);
    } else {
        $initial_image_url = 'https://placehold.co/600x400/EBF4FF/7F9CF5?text=Image+Not+Available';
    }
@endphp

<div class="container mx-auto px-4 py-8">
    <div x-data="productViewComponent({
            product: {{ $product_json }},
            swatchImages: {{ $swatch_images_json }},
            variantImages: {{ $variant_images_json }},
            initialPrice: '{{ $initial_price_display }}',
            initialImage: '{{ $initial_image_url }}'
        })"
        x-init="init()"
        class="bg-white rounded-lg shadow-md p-6 lg:flex lg:space-x-8">

        {{-- Image Section --}}
        <div class="lg:w-1/2">
            <div class="mb-4">
                <img :src="activeImage" 
                     onerror="this.onerror=null; this.src='https://placehold.co/600x400/EBF4FF/7F9CF5?text=Image+Not+Found'" 
                     alt="{{ $product->name }}" 
                     class="w-full h-96 object-contain rounded-lg">
            </div>
            
            <div class="flex flex-wrap gap-2">
                @if($product->image)
                    <div @click="activeImage = '{{ asset('storage/' . $product->image) }}'"
                         class="w-16 h-16 rounded-md cursor-pointer border-2 transition-all"
                         :class="{ 'border-blue-500': activeImage === '{{ asset('storage/' . $product->image) }}' }">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="Product thumbnail" class="w-full h-full object-cover rounded">
                    </div>
                @endif
                @foreach ($product->images as $image)
                    <div @click="activeImage = '{{ asset('storage/' . $image->path) }}'"
                         class="w-16 h-16 rounded-md cursor-pointer border-2 transition-all"
                         :class="{ 'border-blue-500': activeImage === '{{ asset('storage/' . $image->path) }}' }">
                        <img src="{{ asset('storage/' . $image->path) }}" alt="Product thumbnail" class="w-full h-full object-cover rounded">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Product Details Section --}}
        <div class="lg:w-1/2 mt-6 lg:mt-0">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
            <p class="text-gray-600 text-lg mb-2">{{ $product->category->name ?? 'Uncategorized' }}</p>
            
            <p class="text-gray-900 font-bold text-3xl my-6"><span x-text="displayPrice"></span></p>
            
            <p class="text-gray-700 leading-relaxed mb-6">{{ $product->description }}</p>

            {{-- Variations Section --}}
            <div class="border-t pt-4">
                 <template x-for="attribute in attributes" :key="attribute.id">
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                             <label class="block text-sm font-bold text-gray-700" x-text="attribute.name + ':'"></label>
                             <span class="ml-2 text-sm text-gray-800 font-medium" x-text="getSelectedValueName(attribute.name)"></span>
                        </div>
                       
                        <div class="flex flex-wrap gap-2">
                            <template x-for="value in attribute.values" :key="value.id">
                                <button
                                    type="button"
                                    @click="toggleSelection(attribute.name, value.id)"
                                    :disabled="!isOptionAvailable(attribute.name, value.id)"
                                    class="border rounded-md transition-colors duration-200 disabled:opacity-40 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    :class="{
                                        'ring-2 ring-blue-500 border-blue-500': selection[attribute.name] === value.id,
                                        'border-gray-300 hover:border-gray-500': selection[attribute.name] !== value.id
                                    }"
                                >
                                    <template x-if="attribute.name.toLowerCase() === 'color' && swatchImages[value.id]">
                                        <img :src="swatchImages[value.id]" :alt="value.value" class="w-10 h-10 object-cover rounded-md p-0.5">
                                    </template>
                                    <template x-if="attribute.name.toLowerCase() !== 'color' || !swatchImages[value.id]">
                                        <span class="px-3 py-1 block" x-text="value.value"></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t pt-4 mt-4">
                <div class="flex items-center text-gray-700 mb-2"><span class="font-semibold mr-2">SKU:</span> <span x-text="displaySku"></span></div>
                <div class="flex items-center text-gray-700 mb-6"><span class="font-semibold mr-2">Availability:</span> <span x-html="displayStock"></span></div>
            </div>
            
            <div class="flex items-start space-x-2 mt-2">
                <form action="{{ route('cart.add') }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="product_variant_id" :value="selectedVariant ? selectedVariant.id : ''">
                    <input type="number" name="quantity" value="1" min="1" :max="availableStock" class="form-input w-24 text-center border-gray-300 rounded-md shadow-sm" :disabled="!isSelectionComplete || availableStock === 0">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-lg" :disabled="!isSelectionComplete || availableStock === 0">
                        <span x-text="addToCartText"></span>
                    </button>
                </form>

                @auth
                <form action="{{ route('wishlist.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="product_variant_id" :value="selectedVariant ? selectedVariant.id : ''">
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg text-lg" :disabled="product.variants.length > 0 && !isSelectionComplete">
                        Add to Wishlist
                    </button>
                </form>
                @endauth
                <p x-show="product.variants.length > 0 && !isSelectionComplete" class="text-red-500 text-sm mt-3">Please select all options to add to cart.</p>
            </div>
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
        product: data.product,
        swatchImages: data.swatchImages,
        attributeImages: data.attributeImages,
        variantImages: data.variantImages,
        activeImage: data.initialImage,
        initialPrice: data.initialPrice,
        attributes: [], 
        selection: {}, 
        selectedVariant: null,

        get displayPrice() {
            if (this.isSelectionComplete && this.selectedVariant) {
                return '$' + parseFloat(this.selectedVariant.price).toFixed(2);
            }
            return this.initialPrice;
        },
        get displaySku() {
            return this.selectedVariant ? this.selectedVariant.sku : this.product.sku || 'N/A';
        },
        get availableStock() {
            if (this.product.variants.length > 0) {
                return this.isSelectionComplete && this.selectedVariant ? parseInt(this.selectedVariant.stock_quantity) : 0;
            }
            return parseInt(this.product.stock_quantity);
        },
        get displayStock() {
            if (this.product.variants.length > 0) {
                if (this.isSelectionComplete) {
                    if (this.selectedVariant) {
                        const stock = parseInt(this.selectedVariant.stock_quantity);
                        return stock > 0 ? `<span class="text-green-600">${stock} in stock</span>` : `<span class="text-red-600">Out of Stock</span>`;
                    } else {
                        return `<span class="text-red-600">Combination not available</span>`;
                    }
                }
                return `<span class="text-blue-600">Please select options</span>`;
            }
            const stock = parseInt(this.product.stock_quantity);
            return stock > 0 ? `<span class="text-green-600">${stock} in stock</span>` : `<span class="text-red-600">Out of Stock</span>`;
        },
        get isSelectionComplete() {
            if (this.attributes.length === 0) return true;
            return this.attributes.every(attr => this.selection[attr.name]);
        },
        get addToCartText() {
            if (this.product.variants.length > 0 && !this.isSelectionComplete) return 'Select Options';
            return this.availableStock > 0 ? 'Add to Cart' : 'Out of Stock';
        },
        
        init() {
            if (this.product.variants.length > 0) {
                this.processAttributes();
                this.$watch('selection', () => this.updateState());
            }
        },
        processAttributes() {
            const attributesMap = {};
            this.product.variants.forEach(variant => {
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
            this.attributes = Object.values(attributesMap).sort((a,b) => a.name.localeCompare(b.name));
        },
        updateState() {
            this.selectedVariant = null;
            if (this.isSelectionComplete) {
                this.selectedVariant = this.product.variants.find(variant => 
                    this.attributes.every(attr => 
                        variant.attribute_values.some(av => av.id == this.selection[attr.name])
                    )
                ) || null;
            }
            this.updateActiveImage();
        },
        updateActiveImage() {
            if (this.selectedVariant && this.variantImages[this.selectedVariant.id]) {
                this.activeImage = `{{ asset('storage') }}/${this.variantImages[this.selectedVariant.id]}`;
                return;
            }
            const colorAttr = this.attributes.find(a => a.name.toLowerCase() === 'color');
            if (colorAttr) {
                const selectedColorValueId = this.selection[colorAttr.name];
                if (selectedColorValueId && this.attributeImages[selectedColorValueId]) {
                    this.activeImage = `{{ asset('storage') }}/${this.attributeImages[selectedColorValueId]}`;
                    return;
                }
            }
            this.activeImage = data.initialImage;
        },
        isOptionAvailable(attributeName, valueId) {
            let tempSelection = { ...this.selection };
            tempSelection[attributeName] = valueId;
            return this.product.variants.some(variant => {
                return Object.entries(tempSelection).every(([key, val]) => {
                    if (!val) return true;
                    return variant.attribute_values.some(av => av.id == val && av.attribute.name === key);
                });
            });
        },
        getSelectedValueName(attributeName) {
            const selectedValueId = this.selection[attributeName];
            if (!selectedValueId) return '';
            const attribute = this.attributes.find(a => a.name === attributeName);
            const valueObject = Object.values(attribute.values).find(v => v.id == selectedValueId);
            return valueObject ? valueObject.value : '';
        },
        toggleSelection(attributeName, valueId) {
            if (this.selection[attributeName] === valueId) {
                this.selection[attributeName] = '';
            } else {
                this.selection[attributeName] = valueId;
            }
        }
    }
}
</script>
@endpush