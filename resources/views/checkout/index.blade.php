@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Checkout</h1>

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

    @if (empty($cartDetails))
        <p class="text-lg text-gray-600">Your cart is empty. Please add products before checking out.</p>
        <a href="{{ route('cart.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Cart</a>
    @else
        <div class="bg-white shadow-md rounded-lg p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Order Summary --}}
            <div class="lg:col-span-2">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Summary</h2>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($cartDetails as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($item['image'])
                                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="h-12 w-12 object-cover rounded-full mr-4">
                                            @endif
                                            <div>
                                                <span class="text-gray-900 font-medium">{{ $item['name'] }}</span>
                                                {{-- UPDATE: Display variant attributes if they exist --}}
                                                @if (!empty($item['variant_name']))
                                                    <div class="text-xs text-gray-500">{{ $item['variant_name'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $item['sku'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">${{ number_format($item['price'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $item['quantity'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">${{ number_format($item['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end items-center mb-6">
                    <span class="text-xl font-bold mr-4">Cart Subtotal:</span>
                    <span class="text-xl font-bold">${{ number_format($subtotal, 2) }}</span>
                </div>
            </div>

            {{-- Shipping & Payment Details --}}
            <div class="lg:col-span-1">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Shipping & Payment</h2>
                <form action="{{ route('checkout.process') }}" method="POST" id="payment-form">
                    @csrf
                    <div class="mb-4">
                        <label for="shipping_address" class="block text-gray-700 text-sm font-bold mb-2">Shipping Address:</label>
                        <textarea name="shipping_address" id="shipping_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>{{ old('shipping_address', $shippingAddress) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="billing_address" class="block text-gray-700 text-sm font-bold mb-2">Billing Address (Optional):</label>
                        <textarea name="billing_address" id="billing_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">{{ old('billing_address', $billingAddress) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                        <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('email', $email) }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
                        <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('phone', $phone) }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="shipping_method_id" class="block text-gray-700 text-sm font-bold mb-2">Shipping Method:</label>
                        <select name="shipping_method_id" id="shipping_method_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            @forelse ($shippingMethods as $method)
                                <option value="{{ $method->id }}" data-cost="{{ $method->cost }}" {{ old('shipping_method_id') == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }} (${{ number_format($method->cost, 2) }})
                                </option>
                            @empty
                                <option value="">No shipping methods available</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="payment_method" class="block text-gray-700 text-sm font-bold mb-2">Payment Method:</label>
                        <select name="payment_method" id="payment_method" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                            <option value="stripe" {{ old('payment_method') == 'stripe' ? 'selected' : '' }}>Credit Card (Stripe)</option>
                        </select>
                    </div>
                    <div id="stripe-card-element-container" class="mb-4 {{ old('payment_method') == 'stripe' ? '' : 'hidden' }}">
                        {{-- Stripe elements will be inserted here --}}
                    </div>
                    <div class="mb-6">
                        <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Order Notes (Optional):</label>
                        <textarea name="notes" id="notes" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">{{ old('notes') }}</textarea>
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Shipping: <span id="shipping-cost-display">${{ number_format($shippingMethods->first()->cost ?? 0, 2) }}</span></h3>
                        <h3 class="text-xl font-bold">Total: <span id="final-total-display">${{ number_format($subtotal + ($shippingMethods->first()->cost ?? 0), 2) }}</span></h3>
                    </div>
                    <button type="submit" id="submit-button" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-xl w-full">
                        Place Order
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('cashier.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');

    cardElement.mount('#card-element');

    const paymentForm = document.getElementById('payment-form');
    const paymentMethodSelect = document.getElementById('payment_method');
    const shippingMethodSelect = document.getElementById('shipping_method_id');
    const stripeCardElementContainer = document.getElementById('stripe-card-element-container');
    const cardErrors = document.getElementById('card-errors');
    const submitButton = document.getElementById('submit-button');

    const subtotal = {{ $subtotal }};
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const finalTotalDisplay = document.getElementById('final-total-display');

    function updateTotals() {
        let selectedShippingCost = parseFloat(shippingMethodSelect.options[shippingMethodSelect.selectedIndex].dataset.cost || 0);
        let currentTotal = subtotal + selectedShippingCost;
        shippingCostDisplay.textContent = '$' + selectedShippingCost.toFixed(2);
        finalTotalDisplay.textContent = '$' + currentTotal.toFixed(2);
    }

    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'stripe') {
            stripeCardElementContainer.classList.remove('hidden');
            document.getElementById('stripe_payment_method_id').disabled = false;
        } else {
            stripeCardElementContainer.classList.add('hidden');
            document.getElementById('stripe_payment_method_id').disabled = true; 
        }
    });

    shippingMethodSelect.addEventListener('change', updateTotals);
    updateTotals(); // Initial total calculation on page load


    paymentForm.addEventListener('submit', async function(event) {
        if (paymentMethodSelect.value === 'stripe') {
            event.preventDefault(); 
            submitButton.disabled = true;
            cardErrors.textContent = ''; 

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

                const formData = new FormData(paymentForm);

                try {
                    const response = await fetch(paymentForm.action, {
                        method: paymentForm.method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData,
                    });

                    if (response.ok) {
                        const responseData = await response.json();

                        if (responseData.success) {
                            window.location.href = responseData.redirect; 
                        } else if (responseData.requires_action) {

                            const result = await stripe.handleCardAction(responseData.payment_intent_client_secret);
                            if (result.error) {
                                cardErrors.textContent = result.error.message;
                                submitButton.disabled = false;
                            } else {
                                if(responseData.redirect) {
                                     window.location.href = responseData.redirect;
                                }
                            }
                        } else {
                             let errorMessage = 'An unexpected error occurred. Please try again.';
                             if (responseData.errors) {
                                 errorMessage = Object.values(responseData.errors).flat().join('<br>');
                             } else if (responseData.message) {
                                 errorMessage = responseData.message;
                             }
                             cardErrors.innerHTML = errorMessage;
                             submitButton.disabled = false;
                        }
                    } else {
                        let errorMessage = 'An unexpected error occurred. Please try again.';
                        try {
                            const errorData = await response.json();
                            if (errorData.errors) {
                                errorMessage = Object.values(errorData.errors).flat().join('<br>');
                            } else if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            errorMessage = 'Server error: ' + response.statusText;
                        }
                        cardErrors.innerHTML = errorMessage;
                        submitButton.disabled = false;
                    }
                } catch (error) {
                    console.error('Fetch error during payment:', error);
                    cardErrors.textContent = 'A network error occurred. Please try again.';
                    submitButton.disabled = false;
                }
            }
        }
    });
</script>
@endpush