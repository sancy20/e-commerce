<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant; // Import ProductVariant model
use App\Models\ShippingMethod; // Import ShippingMethod model
use App\Models\User;         // Import User model (for vendor's commission rate)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Auth; // To get the authenticated user
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Mail; // For email notifications
use Stripe\StripeClient;             // Import StripeClient
use App\Mail\OrderConfirmationMail;   // For order confirmation email
use App\Mail\NewOrderNotificationMail; // For new order notifications
use App\Notifications\NewOrderNotification; // For database notifications

class CheckoutController extends Controller
{
    /**
     * Display the checkout form.
     */
    public function index()
    {
        $cartItems = Session::get('cart', []);
        $subtotal = 0;
        $cartDetails = [];

        foreach ($cartItems as $itemIdentifier => $item) {
            // Determine if it's a variant or base product
            $isVariant = $item['is_variant'];
            $id = $isVariant ? $item['product_variant_id'] : $item['product_id'];

            if ($isVariant) {
                $itemModel = ProductVariant::find($id);
            } else {
                $itemModel = Product::find($id);
            }

            // Re-check stock before checkout
            if (!$itemModel || $itemModel->stock_quantity < $item['quantity']) {
                Session::forget("cart.$itemIdentifier"); // Remove problematic item from cart
                return redirect()->route('cart.index')->with('error', 'One or more items in your cart are out of stock or unavailable. Please review your cart.');
            }

            $itemSubtotal = $item['price'] * $item['quantity'];
            $subtotal += $itemSubtotal;

            $cartDetails[] = [
                'product_id' => $item['product_id'],
                'product_variant_id' => $isVariant ? $item['product_variant_id'] : null,
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'image' => $item['image'],
                'sku' => $item['sku'],
                'is_variant' => $isVariant,
                'subtotal' => $itemSubtotal,
                'item_identifier' => $itemIdentifier
            ];
        }

        if (empty($cartDetails)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Please add products before checking out.');
        }

        // Pre-fill user details if logged in
        $user = Auth::user();
        $shippingAddress = $user->address ?? '';
        $billingAddress = $user->address ?? '';
        $email = $user->email ?? '';
        $phone = $user->phone ?? '';

        $shippingMethods = ShippingMethod::where('is_active', true)->orderBy('cost')->get(); // Fetch active methods

        return view('checkout.index', compact('cartDetails', 'subtotal', 'shippingAddress', 'billingAddress', 'email', 'phone', 'shippingMethods'));
    }

    /**
     * Process the order.
     */
    public function process(Request $request)
    {
        $validatedData = $request->validate([
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:cash_on_delivery,stripe',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'notes' => 'nullable|string|max:500',
            // stripe_payment_method_id is disabled if not stripe, so not required
            'stripe_payment_method_id' => 'nullable|string',
        ]);

        $cartItems = Session::get('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotalAmount = 0;
        $productsToProcess = []; // To hold base products or variants for stock check/decrement

        foreach ($cartItems as $itemIdentifier => $item) {
            $isVariant = $item['is_variant'];
            $id = $isVariant ? $item['product_variant_id'] : $item['product_id'];

            if ($isVariant) {
                $itemModel = ProductVariant::find($id);
            } else {
                $itemModel = Product::find($id);
            }

            // Crucial stock check just before processing
            if (!$itemModel || $itemModel->stock_quantity < $item['quantity']) {
                DB::rollBack(); // Ensure rollback if any stock issue on re-check
                Session::forget('cart'); // Clear cart as it's now invalid
                Log::error('Checkout failed due to insufficient stock for item: ' . ($item['name'] ?? 'N/A') . ' ID: ' . ($itemModel->id ?? 'N/A'));
                return redirect()->route('cart.index')->with('error', 'One or more items in your cart are out of stock or unavailable. Please review your cart.');
            }

            $productsToProcess[$itemIdentifier] = $itemModel; // Store the actual model (Product or ProductVariant)
            $subtotalAmount += $item['price'] * $item['quantity'];
        }

        $shippingMethod = ShippingMethod::find($validatedData['shipping_method_id']);
        if (!$shippingMethod || !$shippingMethod->is_active) {
            return redirect()->back()->with('error', 'Selected shipping method is invalid or inactive.');
        }

        $finalTotalAmount = $subtotalAmount + $shippingMethod->cost;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $finalTotalAmount,
                'shipping_cost' => $shippingMethod->cost,
                'shipping_method_id' => $shippingMethod->id,
                'order_status' => 'pending',
                'payment_status' => 'pending', // Will be updated after payment attempt
                'shipping_address' => $validatedData['shipping_address'],
                'billing_address' => $validatedData['billing_address'] ?? $validatedData['shipping_address'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'],
            ]);
            Log::info('Order ID: ' . $order->id . ' created. Starting payment process...');

            // Add Order Items and update product/variant stock
            foreach ($cartItems as $itemIdentifier => $item) {
                $itemModel = $productsToProcess[$itemIdentifier]; // Get the pre-fetched model
                $product = $itemModel instanceof ProductVariant ? $itemModel->product : $itemModel; // Get base product
                $vendor = $product->vendor; // Get the vendor of this product

                $itemPrice = (float)$item['price'];
                $itemQuantity = (int)$item['quantity'];
                $itemSubtotal = $itemPrice * $itemQuantity;

                // Calculate commission
                $commissionRate = ($vendor && $vendor->isVendor()) ? $vendor->getCommissionRate() : 0.0000;
                $commissionAmount = $itemSubtotal * $commissionRate;
                $vendorPayoutAmount = $itemSubtotal - $commissionAmount;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $item['is_variant'] ? $item['product_variant_id'] : null,
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                    'commission_amount' => $commissionAmount,
                    'vendor_payout_amount' => $vendorPayoutAmount,
                ]);

                // Decrement stock on the CORRECT model (Product or ProductVariant)
                $itemModel->decrement('stock_quantity', $itemQuantity);
            }

            // --- Stripe Payment Processing Logic ---
            if ($validatedData['payment_method'] === 'stripe') {
                $user = Auth::user();

                try {
                    $paymentIntent = $user->charge(
                        $finalTotalAmount * 100, // Amount in cents
                        $validatedData['stripe_payment_method_id'],
                        [
                            'currency' => 'usd',
                            'description' => 'Order ' . $order->order_number,
                            'setup_future_usage' => 'off_session',
                            'confirm' => true,
                            'return_url' => route('checkout.confirmation', $order),
                            'metadata' => ['order_id' => $order->id], // Pass order ID for webhook
                        ]
                    );

                    if ($paymentIntent->status === 'succeeded') {
                        $order->payment_status = 'paid';
                        $order->order_status = 'processing';
                        Log::info('Stripe payment succeeded for order ID: ' . $order->id . ' PI: ' . $paymentIntent->id);
                    } elseif ($paymentIntent->status === 'requires_action') {
                        DB::rollBack();
                        Log::info('Stripe payment requires action for order ID: ' . $order->id . ' PI: ' . $paymentIntent->id);
                        return response()->json([
                            'success' => false,
                            'requires_action' => true,
                            'payment_intent_client_secret' => $paymentIntent->client_secret,
                        ]);
                    } else {
                        $order->payment_status = 'failed';
                        Log::warning('Stripe payment status for order ID: ' . $order->id . ' is ' . $paymentIntent->status);
                    }

                } catch (\Stripe\Exception\CardException $e) {
                    $order->payment_status = 'failed';
                    DB::rollBack();
                    Log::error('Stripe Card Declined for order ID: ' . $order->id . ': ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Card declined: ' . $e->getMessage());
                } catch (\Exception $e) {
                    $order->payment_status = 'failed';
                    DB::rollBack();
                    Log::error('Stripe Payment Error for order ID: ' . $order->id . ': ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
                }
            } else { // Cash on Delivery or other non-Stripe methods
                $order->payment_status = 'pending';
                Log::info('Payment method is COD for order ID: ' . $order->id . '. Status pending.');
            }

            $order->save(); // Save the final order status (paid/pending/failed)

            // --- Dispatch Email Notifications ---
            if ($order->user->email) {
                Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
                Log::info('Order confirmation email sent to customer for order ID: ' . $order->id);
            }

            $adminEmail = config('mail.from.address'); // Use general admin email
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new NewOrderNotificationMail($order, 'admin')); // Email
                $adminUsers = User::where('is_admin', true)->get(); // Database notification
                foreach ($adminUsers as $admin) {
                    $admin->notify(new NewOrderNotification($order, 'admin'));
                }
                Log::info('New order notification sent to admin (email/db) for order ID: ' . $order->id);
            }

            $notifiedVendors = [];
            foreach ($order->orderItems as $item) {
                $product = $item->product;
                $vendor = $product->vendor;
                if ($vendor && $vendor->isVendor() && !in_array($vendor->id, $notifiedVendors)) {
                    $vendorItems = $order->orderItems->filter(fn($oi) => $oi->product->vendor_id === $vendor->id);
                    if ($vendor->email) {
                        Mail::to($vendor->email)->send(new NewOrderNotificationMail($order, 'vendor', $vendorItems)); // Email
                    }
                    $vendor->notify(new NewOrderNotification($order, 'vendor', $vendorItems)); // Database notification
                    $notifiedVendors[] = $vendor->id;
                }
            }
            Log::info('New order notification sent to vendors (email/db) for order ID: ' . $order->id);


            Session::forget('cart'); // Clear the cart after successful order processing
            DB::commit();
            Log::info('DB commit successful for order ID: ' . $order->id . '. Redirecting to confirmation page.');
            return redirect()->route('checkout.confirmation', $order)->with('success', 'Your order has been placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Outer transaction error during checkout for order ID: ' . ($order->id ?? 'N/A') . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }

    /**
     * Display order confirmation.
     */
    public function confirmation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        $order->load('orderItems.product', 'shippingMethod'); // Eager load order items and their products

        return view('checkout.confirmation', compact('order'));
    }
}