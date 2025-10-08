@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 lg:flex lg:space-x-8">
        <div class="lg:w-1/2">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" id="product-main-image" class="w-full h-96 object-contain rounded-lg">
            @else
                <div class="w-full h-96 bg-gray-200 flex items-center justify-center text-gray-500 text-2xl rounded-lg">No Image</div>
            @endif
        </div>
        <div class="lg:w-1/2 mt-6 lg:mt-0">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
            <p class="text-gray-600 text-lg mb-2">{{ $product->category->name ?? 'Uncategorized' }}</p>

            {{-- Average Rating Display --}}
            @php
                $averageRating = number_format($product->averageRating(), 1);
                $reviewCount = $product->approvedReviews->count();
            @endphp
            <div class="flex items-center mb-4">
                @if ($reviewCount > 0)
                    <span class="text-yellow-500 text-xl mr-2">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= floor($averageRating))
                                &#9733;
                            @elseif ($i - 0.5 <= $averageRating)
                                &#9733;
                            @else
                                &#9734;
                            @endif
                        @endfor
                    </span>
                    <span class="text-gray-700 text-sm">({{ $averageRating }} / 5 from {{ $reviewCount }} reviews)</span>
                @else
                    <span class="text-gray-500 text-sm">No reviews yet.</span>
                @endif
            </div>

            <p class="text-gray-900 font-bold text-3xl mb-6">
                <span id="product-price-display">${{ number_format($product->effective_price, 2) }}</span>
            </p>

            <p class="text-gray-700 leading-relaxed mb-6">{{ $product->description }}</p>

            <div class="flex items-center text-gray-700 mb-4">
                <span class="font-semibold mr-2">SKU:</span>
                <span id="product-sku-display">{{ $product->sku ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center text-gray-700 mb-6">
                <span class="font-semibold mr-2">Availability:</span>
                <span id="product-stock-display">
                    @if ($product->hasVariants())
                        <span class="text-red-600">Please select options</span>
                    @elseif ($product->effective_stock_quantity > 0)
                        <span class="text-green-600">{{ $product->effective_stock_quantity }} in stock</span>
                        @if ($product->effective_stock_quantity < 5)
                            <span class="ml-2 text-orange-500 text-sm">(Low stock!)</span>
                        @endif
                    @else
                        <span class="text-red-600">Out of Stock</span>
                    @endif
                </span>
            </div>

            <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" id="product_variant_id_input" value=""> {{-- HIDDEN INPUT FOR VARIANT ID --}}

                {{-- VARIANT SELECTION DROPDOWNS --}}
                @if ($product->hasVariants())
                    @php
                        // Group attribute values by attribute name to display dropdowns
                        $groupedAttributes = $product->variants->flatMap(function($variant) {
                            return $variant->attributeValues->map(function($av) use ($variant) {
                                return ['attribute_name' => $av->attribute->name, 'id' => $av->id, 'value' => $av->value, 'variant_id' => $variant->id];
                            });
                        })->groupBy('attribute_name');
                    @endphp

                    @foreach ($attributes->sortBy('name') as $attribute)
                        @if ($groupedAttributes->has($attribute->name))
                            <div class="mb-4">
                                <label for="attribute-{{ $attribute->slug }}" class="block text-gray-700 text-sm font-bold mb-2">{{ $attribute->name }}:</label>
                                <select name="attribute_values[{{ $attribute->id }}]" id="attribute-{{ $attribute->slug }}" data-attribute-id="{{ $attribute->id }}" class="attribute-selector shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-2" required>
                                    <option value="">Select {{ $attribute->name }}</option>
                                    @foreach ($groupedAttributes[$attribute->name]->unique('id') as $value) {{-- Display unique values for this attribute --}}
                                        <option value="{{ $value['id'] }}">{{ $value['value'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach
                    <p id="variant-selection-message" class="text-red-500 text-sm mb-4 hidden">Please select all options.</p>
                @endif
                {{-- END VARIANT SELECTION --}}

                <div class="flex items-center mt-4">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->hasVariants() ? 0 : $product->effective_stock_quantity }}"
                        class="form-input w-24 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-4"
                        id="quantity-input"
                        @if ($product->hasVariants() || $product->effective_stock_quantity <= 0) disabled @endif>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg"
                        id="add-to-cart-button"
                        @if ($product->hasVariants() || $product->effective_stock_quantity <= 0) disabled @endif>
                        @if ($product->hasVariants())
                            Select Options
                        @elseif ($product->effective_stock_quantity <= 0)
                            Out of Stock
                        @else
                            Add to Cart
                        @endif
                    </button>
                </div>
            </form>

            {{-- Add to Wishlist Button --}}
            @auth
                <form action="{{ route('wishlist.add') }}" method="POST" id="add-to-wishlist-form" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="product_variant_id" id="product_variant_id_wishlist_input" value=""> {{-- This will be populated by JS --}}
                    <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-lg" id="add-to-wishlist-button">
                        Add to Wishlist
                    </button>
                </form>
            @endauth

            <div class="mt-8">
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Products
                </a>
                @if ($product->vendor && $product->vendor->isVendor() && Auth::check() && Auth::id() !== $product->vendor->id) {{-- Only if logged in, not seller themselves --}}
                    <a href="{{ route('inquiries.create', $product->id) }}" class="inline-block bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-center text-sm">
                        Contact Seller
                    </a>
                @elseif(Auth::guest())
                     <p class="text-gray-600 text-sm mt-2">
                        <a href="{{ route('login') }}" class="text-indigo-500 hover:underline">Log in</a> to contact the seller.
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Reviews Section --}}
    <div class="mt-12 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Reviews</h2>

        {{-- Review Submission Form (remains same) --}}
        @auth
            @php
                $hasReviewed = $product->reviews()->where('user_id', Auth::id())->exists();
            @endphp
            @if (!$hasReviewed)
                <div class="mb-8 p-6 border border-gray-200 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Submit Your Review</h3>
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

                    <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                            <select name="rating" id="rating" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('rating') border-red-500 @enderror" required>
                                <option value="">Select a rating</option>
                                <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>&#9733;&#9733;&#9733;&#9733;&#9733; (5 Stars)</option>
                                <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>&#9733;&#9733;&#9733;&#9733; (4 Stars)</option>
                                <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>&#9733;&#9733;&#9733; (3 Stars)</option>
                                <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>&#9733;&#9733; (2 Stars)</option>
                                <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>&#9733; (1 Star)</option>
                            </select>
                            @error('rating')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment (Optional):</label>
                            <textarea name="comment" id="comment" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('comment') border-red-500 @enderror">{{ old('comment') }}</textarea>
                            @error('comment')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
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

        {{-- Display Approved Reviews --}}
        @if ($product->approvedReviews->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-xl font-semibold mb-4">What Customers Are Saying:</h3>
                @foreach ($product->approvedReviews->sortByDesc('created_at') as $review)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-center mb-2">
                            <p class="font-semibold mr-2">{{ $review->user->name ?? 'Anonymous User' }}</p>
                            <span class="text-yellow-500">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating) &#9733; @else &#9734; @endif
                                @endfor
                            </span>
                            <span class="text-gray-500 text-sm ml-auto">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="text-gray-700 mb-2">{{ $review->comment }}</p>
                        @endif

                        {{-- DISPLAY VENDOR'S REPLY --}}
                        @if ($review->vendor_reply && $review->product->vendor)
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="font-semibold text-blue-800 mb-1">Reply from {{ $review->product->vendor->business_name ?? $review->product->vendor->name }}:</p>
                                <p class="text-blue-700 text-sm">{{ $review->vendor_reply }}</p>
                                <p class="text-sm text-gray-500 mt-1">Replied on: {{ $review->replied_at->format('M d, Y H:i A') }}</p>
                            </div>
                        @endif
                        {{-- END VENDOR REPLY DISPLAY --}}
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
    // DEBUG: Show product data in console
    console.log('=== PRODUCT DEBUG INFO ===');
    console.log('Product has variants:', {{ $product->hasVariants() ? 'true' : 'false' }});
    console.log('Product base stock:', {{ $product->stock_quantity }});
    console.log('Product effective stock:', {{ $product->effective_stock_quantity }});
    
    // Pass product data from Blade to JavaScript
    const productVariants = @json($product->variants->load('attributeValues.attribute')); // Ensure full variant data is loaded
    const attributes = @json(\App\Models\Attribute::with('values')->get()); // All possible attributes and their values

    console.log('Product variants from server:', productVariants);
    console.log('All attributes from server:', attributes);
    
    // Get DOM elements
    const attributeSelectors = document.querySelectorAll('.attribute-selector');
    const productPriceDisplay = document.getElementById('product-price-display');
    const productSkuDisplay = document.getElementById('product-sku-display');
    const productStockDisplay = document.getElementById('product-stock-display');
    const quantityInput = document.getElementById('quantity-input');
    const addToCartButton = document.getElementById('add-to-cart-button');
    const productVariantIdInput = document.getElementById('product_variant_id_input');
    const variantSelectionMessage = document.getElementById('variant-selection-message');
    const productMainImage = document.getElementById('product-main-image');
    
    // Debug elements
    const debugSelected = document.getElementById('debug-selected');
    const debugVariants = document.getElementById('debug-variants');
    const debugMatch = document.getElementById('debug-match');
    
    // Debug: Log the actual HTML of selectors
    console.log('=== DROPDOWN HTML DEBUG ===');
    document.querySelectorAll('.attribute-selector').forEach((selector, index) => {
        console.log(`Selector ${index}:`, selector.outerHTML);
        console.log(`Options:`, Array.from(selector.options).map(opt => ({value: opt.value, text: opt.text})));
    });
    
    // Update debug display
    if (debugVariants) {
        debugVariants.innerHTML = `Variants: ${productVariants.length} found`;
    }

    let selectedAttributeValues = {}; // Stores { attribute_id: attribute_value_id }
    const initialBasePrice = {{ $product->price }}; // Base product price for fallback
    const initialBaseSku = "{{ $product->sku }}"; // Base product SKU for fallback
    const initialBaseStock = {{ $product->stock_quantity }}; // Base product stock for fallback
    const initialBaseImage = "{{ $product->image ? asset('storage/' . $product->image) : '' }}";

    function updateVariantDisplay() {
        let matchingVariant = null;
        let currentSelectedValues = Object.values(selectedAttributeValues).filter(value => value !== null && value !== '' && value !== undefined);

        // Check if all required selectors have a value chosen
        const allSelectorsFilled = attributeSelectors.length > 0 && Array.from(attributeSelectors).every(selector => selector.value !== '');

        if (allSelectorsFilled && currentSelectedValues.length > 0) {
            variantSelectionMessage.classList.add('hidden');
            
            // Find matching variant
            for (const variant of productVariants) {
                const variantAttributeValueIds = variant.attribute_values.map(av => parseInt(av.id));
                const selectedIntValues = currentSelectedValues.map(val => parseInt(val));
                
                // Check if all selected values are present in the variant's attributes
                const isMatch = selectedIntValues.every(valId => variantAttributeValueIds.includes(valId));
                
                // Also ensure the number of attributes matches
                if (isMatch && variantAttributeValueIds.length === selectedIntValues.length) {
                    matchingVariant = variant;
                    break;
                }
            }
        } else if (attributeSelectors.length > 0) { // If there are selectors but not all filled
            variantSelectionMessage.classList.remove('hidden');
        }

        // Update debug display
        if (debugSelected) {
            debugSelected.innerHTML = `Selected: ${JSON.stringify(selectedAttributeValues)}`;
        }
        if (debugMatch) {
            debugMatch.innerHTML = `Match: ${matchingVariant ? 'Variant ' + matchingVariant.id : 'None'}`;
        }


        if (matchingVariant) {
            console.log('Updating UI for matching variant:', matchingVariant);
            
            // Handle price conversion (ensure it's a number)
            const variantPrice = matchingVariant.price !== null ? parseFloat(matchingVariant.price) : initialBasePrice;
            productPriceDisplay.textContent = '$' + variantPrice.toFixed(2);
            
            productSkuDisplay.textContent = matchingVariant.sku || initialBaseSku || 'N/A';
            productStockDisplay.innerHTML = getStockDisplayHtml(matchingVariant.stock_quantity);
            
            // Enable quantity input and set proper limits
            quantityInput.disabled = false;
            quantityInput.max = matchingVariant.stock_quantity;
            quantityInput.min = 1;
            quantityInput.value = Math.max(1, Math.min(quantityInput.value || 1, matchingVariant.stock_quantity));
            
            productVariantIdInput.value = matchingVariant.id;
            
            // Enable/disable cart button based on stock
            addToCartButton.disabled = matchingVariant.stock_quantity <= 0;
            addToCartButton.textContent = matchingVariant.stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart';

            // Update product image if variant has one
            if (matchingVariant.image) {
                productMainImage.src = "{{ asset('storage/') }}" + '/' + matchingVariant.image;
            } else if (initialBaseImage) {
                productMainImage.src = initialBaseImage;
            }
            
            console.log('UI updated successfully');
        } else {
            // Reset to base product details if no matching variant is found (or not all attributes selected)
            if (attributeSelectors.length > 0 && !allSelectorsFilled) { 
                // Product has variants, but selection is incomplete
                productPriceDisplay.textContent = '$' + initialBasePrice.toFixed(2); // Still show base price
                productSkuDisplay.textContent = initialBaseSku || 'N/A';
                productStockDisplay.innerHTML = '<span class="text-red-600">Please select options</span>'; // Prompt selection
                quantityInput.max = 0; // Effectively out of stock
                quantityInput.value = 1;
                quantityInput.disabled = true;
                productVariantIdInput.value = '';
                addToCartButton.disabled = true;
                addToCartButton.textContent = 'Select Options';
                if (initialBaseImage) { productMainImage.src = initialBaseImage; }
            } else if (attributeSelectors.length > 0 && allSelectorsFilled) { 
                // All attributes selected but no matching variant found - this combination doesn't exist
                productPriceDisplay.textContent = '$' + initialBasePrice.toFixed(2);
                productSkuDisplay.textContent = initialBaseSku || 'N/A';
                productStockDisplay.innerHTML = '<span class="text-red-600">This combination is not available</span>';
                quantityInput.max = 0;
                quantityInput.value = 1;
                quantityInput.disabled = true;
                productVariantIdInput.value = '';
                addToCartButton.disabled = true;
                addToCartButton.textContent = 'Not Available';
                if (initialBaseImage) { productMainImage.src = initialBaseImage; }
            } else { 
                // No variants at all - use base product
                productPriceDisplay.textContent = '$' + initialBasePrice.toFixed(2);
                productSkuDisplay.textContent = initialBaseSku || 'N/A';
                productStockDisplay.innerHTML = getStockDisplayHtml(initialBaseStock);
                quantityInput.max = initialBaseStock;
                quantityInput.value = Math.min(quantityInput.value || 1, initialBaseStock);
                quantityInput.disabled = initialBaseStock <= 0;
                productVariantIdInput.value = '';
                addToCartButton.disabled = initialBaseStock <= 0;
                addToCartButton.textContent = initialBaseStock <= 0 ? 'Out of Stock' : 'Add to Cart';
                if (initialBaseImage) { productMainImage.src = initialBaseImage; }
            }
        }
    }

    function getStockDisplayHtml(stock) {
        if (stock > 0) {
            let html = `<span class="text-green-600">${stock} in stock</span>`;
            if (stock < 5) {
                html += `<span class="ml-2 text-orange-500 text-sm">(Low stock!)</span>`;
            }
            return html;
        } else {
            return `<span class="text-red-600">Out of Stock</span>`;
        }
    }


    attributeSelectors.forEach(selector => {
        selector.addEventListener('change', (event) => {
            const attributeId = parseInt(event.target.dataset.attributeId);
            const selectedValue = event.target.value;
            
            if (selectedValue && selectedValue !== '') {
                selectedAttributeValues[attributeId] = parseInt(selectedValue);
            } else {
                selectedAttributeValues[attributeId] = null;
            }
            
            updateVariantDisplay();
        });
    });

    // Initialize display on page load if product has variants
    if (attributeSelectors.length > 0) {
        console.log('Initializing variant selectors...');
        console.log('Found selectors:', attributeSelectors.length);
        
        // Initialize selectedAttributeValues for all attributes
        attributeSelectors.forEach((selector, index) => {
            const attributeId = parseInt(selector.dataset.attributeId);
            selectedAttributeValues[attributeId] = null;
            console.log(`Selector ${index}: attribute ID ${attributeId}, current value: "${selector.value}"`);
            
            // If selector already has a value (pre-selected), capture it
            if (selector.value && selector.value !== '') {
                selectedAttributeValues[attributeId] = parseInt(selector.value);
                console.log(`Pre-selected value found: ${selector.value}`);
            }
        });
        
        // Pre-select options if old input exists (e.g., after validation error)
        @if(old('attribute_values'))
            @foreach(old('attribute_values') as $attributeId => $attributeValueId)
                const selector = document.getElementById('attribute-{{ \App\Models\Attribute::find($attributeId)->slug ?? '' }}');
                if(selector) {
                    selector.value = '{{ $attributeValueId }}';
                    selectedAttributeValues[{{ $attributeId }}] = parseInt('{{ $attributeValueId }}');
                }
            @endforeach
        @endif
        
        console.log('Initial selectedAttributeValues:', selectedAttributeValues);
        console.log('Number of product variants:', productVariants.length);
        
        updateVariantDisplay(); // Call initially to set correct state based on pre-selected values (if any)
    } else {
        console.log('No variant selectors found - product has no variants');
    }
    
    // Manual check function for debugging (move to global scope)
    function manualCheck() {
        console.log('=== MANUAL CHECK ===');
        console.log('Current dropdown values:');
        attributeSelectors.forEach((selector, index) => {
            console.log(`Selector ${index}: ID=${selector.id}, value="${selector.value}", data-attribute-id="${selector.dataset.attributeId}"`);
        });
        
        // Force re-read the values
        attributeSelectors.forEach(selector => {
            const attributeId = parseInt(selector.dataset.attributeId);
            const selectedValue = selector.value;
            
            if (selectedValue && selectedValue !== '') {
                selectedAttributeValues[attributeId] = parseInt(selectedValue);
            } else {
                selectedAttributeValues[attributeId] = null;
            }
        });
        
        console.log('Force-updated selectedAttributeValues:', selectedAttributeValues);
        updateVariantDisplay();
    }
    
    // Make function globally accessible
    window.manualCheck = manualCheck;
</script>
@endpush