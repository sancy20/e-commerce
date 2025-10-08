@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Checkout</h1>

    {{-- SUCCESS/ERROR MESSAGES --}}
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- ADD THIS BLOCK TO DISPLAY VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Cart Summary --}}
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Order Summary</h2>
            @forelse ($cartDetails as $item)
                <div class="flex items-center justify-between py-4 border-b border-gray-200 last:border-b-0">
                    <div class="flex items-center">
                        @if ($item['product']->image || $item['image'])
                            <img src="{{ asset('storage/' . ($item['image'] ?? $item['product']->image)) }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded-md mr-4">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-md mr-4 flex items-center justify-center text-gray-500 text-xs">No Image</div>
                        @endif
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $item['name'] }}</h3>
                            <p class="text-gray-600 text-sm">SKU: {{ $item['sku'] ?? 'N/A' }}</p>
                            <p class="text-gray-600 text-sm">Price: ${{ number_format($item['price'], 2) }} each</p>
                            <p class="text-gray-600 text-sm">Quantity: {{ $item['quantity'] }}</p>
                            @if($item['is_variant'] && isset($item['variant']))
                                <p class="text-blue-600 text-xs">{{ $item['variant']->variant_name ?? 'Variant' }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">${{ number_format($item['subtotal'], 2) }}</span>
                </div>
            @empty
                <p class="text-lg text-gray-600">Your cart is empty.</p>
            @endforelse
            <div class="mt-6 pt-4 border-t border-gray-300">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xl font-bold text-gray-800">Subtotal:</span>
                    <span class="text-xl font-bold text-gray-900">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xl font-bold text-gray-800">Shipping:</span>
                    <span id="shipping-cost-display" class="text-xl font-bold text-gray-900">$0.00</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-2xl font-bold text-gray-800">Total:</span>
                    <span id="final-total-display" class="text-2xl font-bold text-gray-900">${{ number_format($subtotal, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Checkout Form --}}
        <div class="lg:col-span-1">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Shipping & Payment</h2>
            <form id="payment-form" action="{{ route('checkout.process') }}" method="POST">
                @csrf

                {{-- Shipping Address --}}
                <div class="mb-4">
                    <label for="shipping_address" class="block text-gray-700 text-sm font-bold mb-2">Shipping Address:</label>
                    <textarea name="shipping_address" id="shipping_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('shipping_address') border-red-500 @enderror" required>{{ old('shipping_address', $shippingAddress) }}</textarea>
                    @error('shipping_address')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Billing Address (Optional, can be same as shipping) --}}
                <div class="mb-4">
                    <label for="billing_address" class="block text-gray-700 text-sm font-bold mb-2">Billing Address (Optional, leave blank if same as shipping):</label>
                    <textarea name="billing_address" id="billing_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('billing_address') border-red-500 @enderror">{{ old('billing_address', $billingAddress) }}</textarea>
                    @error('billing_address')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact Info --}}
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" value="{{ old('email', $email) }}" required>
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
                    <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror" value="{{ old('phone', $phone) }}" required>
                    @error('phone')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Shipping Method Selection --}}
                <div class="mb-6">
                    <label for="shipping_method_id" class="block text-gray-700 text-sm font-bold mb-2">Shipping Method:</label>
                    <select name="shipping_method_id" id="shipping_method_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('shipping_method_id') border-red-500 @enderror" required>
                        <option value="">Select a shipping method</option>
                        @foreach ($shippingMethods as $method)
                            <option value="{{ $method->id }}" data-cost="{{ $method->cost }}" {{ old('shipping_method_id') == $method->id ? 'selected' : '' }}>
                                {{ $method->name }} (${{ number_format($method->cost, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('shipping_method_id')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Payment Method Selection --}}
                <div class="mb-6">
                    <label for="payment_method" class="block text-gray-700 text-sm font-bold mb-2">Payment Method:</label>
                    <select name="payment_method" id="payment_method" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('payment_method') border-red-500 @enderror" required>
                        <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                        <option value="stripe" {{ old('payment_method') == 'stripe' ? 'selected' : '' }}>Credit Card (Stripe)</option>
                    </select>
                    @error('payment_method')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Stripe Card Element Container --}}
                <div id="stripe-card-element-container" class="mb-6 {{ old('payment_method') == 'stripe' ? '' : 'hidden' }}">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Card Details:</label>
                    <div id="card-element" class="border border-gray-300 p-3 rounded-md">
                        </div>
                    <div id="card-errors" role="alert" class="text-red-500 text-xs italic mt-1"></div>
                    <input type="hidden" name="stripe_payment_method_id" id="stripe_payment_method_id" disabled>
                </div>

                {{-- Notes (Optional) --}}
                <div class="mb-6">
                    <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Order Notes (Optional):</label>
                    <textarea name="notes" id="notes" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" id="submit-button" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline text-xl">
                    Place Order
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize variables first
    const paymentForm = document.getElementById('payment-form');
    const paymentMethodSelect = document.getElementById('payment_method');
    const shippingMethodSelect = document.getElementById('shipping_method_id');
    const stripeCardElementContainer = document.getElementById('stripe-card-element-container');
    const cardErrors = document.getElementById('card-errors');
    const submitButton = document.getElementById('submit-button');
    const subtotal = {{ $subtotal }};
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const finalTotalDisplay = document.getElementById('final-total-display');
    
    // Stripe configuration (only initialize if key exists)
    let stripe = null;
    let cardElement = null;
    const stripeKey = '{{ config('cashier.key') }}';
    
    if (stripeKey && stripeKey !== '') {
        try {
            stripe = Stripe(stripeKey);
            const elements = stripe.elements();
            cardElement = elements.create('card');
            cardElement.mount('#card-element');
        } catch (error) {
            console.error('Stripe initialization failed:', error);
            // Hide Stripe option if it fails to initialize
            document.querySelector('option[value="stripe"]').style.display = 'none';
        }
    } else {
        // Hide Stripe option if no key is configured
        document.querySelector('option[value="stripe"]').style.display = 'none';
        console.warn('Stripe key not configured. Credit card payments are disabled.');
    }

    // Function to update total display
    function updateTotals() {
        const selectedOption = shippingMethodSelect.options[shippingMethodSelect.selectedIndex];
        let selectedShippingCost = parseFloat(selectedOption?.dataset?.cost || 0);
        let currentTotal = subtotal + selectedShippingCost;

        shippingCostDisplay.textContent = '$' + selectedShippingCost.toFixed(2);
        finalTotalDisplay.textContent = '$' + currentTotal.toFixed(2);
    }

    const stripePaymentMethodIdInput = document.getElementById('stripe_payment_method_id');

    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'stripe') {
            if (!stripe) {
                alert('Credit card payment is not available. Please select Cash on Delivery.');
                this.value = 'cash_on_delivery';
                return;
            }
            stripeCardElementContainer.classList.remove('hidden');
            stripePaymentMethodIdInput.disabled = false;
        } else {
            stripeCardElementContainer.classList.add('hidden');
            stripePaymentMethodIdInput.disabled = true;
        }
    });

    paymentMethodSelect.dispatchEvent(new Event('change'));
    shippingMethodSelect.addEventListener('change', updateTotals);
    
    // Initial total calculation on page load
    updateTotals();

    paymentForm.addEventListener('submit', async function(event) {
        if (paymentMethodSelect.value === 'stripe') {
            if (!stripe || !cardElement) {
                alert('Credit card payment is not available. Please select Cash on Delivery.');
                event.preventDefault();
                return;
            }
            
            event.preventDefault();
            submitButton.disabled = true;

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: '{{ Auth::user()->name ?? 'Guest' }}',
                    email: document.getElementById('email').value,
                },
            });

            if (error) {
                cardErrors.textContent = error.message;
                submitButton.disabled = false;
            } else {
                document.getElementById('stripe_payment_method_id').value = paymentMethod.id;
                paymentForm.submit();
            }
        }
    });

    @if(session('requires_action'))
        const clientSecret = '{{ session('payment_intent_client_secret') }}';
        if (stripe) {
            stripe.handleCardAction(clientSecret).then(function(result) {
                if (result.error) {
                    alert(result.error.message);
                } else {
                    window.location.href = '{{ route('checkout.confirmation', $order ?? '') }}';
                }
            });
        }
    @endif
</script>
@endpush