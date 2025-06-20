<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Notifications\NewOrderNotification;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Session::get('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $cartDetails = [];
        $subtotal = 0;
        
        foreach ($cartItems as $identifier => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $price = $product->price;
            $stock = $product->stock_quantity;
            $variant = null;

            if (!empty($item['product_variant_id'])) {
                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $price = $variant->price ?? $price;
                    $stock = $variant->stock_quantity;
                }
            }
            
            if ($stock < $item['quantity']) {
                 return redirect()->route('cart.index')->with('error', "Sorry, '{$product->name}' is out of stock. Please remove it from your cart.");
            }

            $subtotal += $price * $item['quantity'];
        }
        
        $user = Auth::user();
        $shippingAddress = $user->address ?? '';
        $billingAddress = $user->address ?? '';
        $email = $user->email ?? '';
        $phone = $user->phone ?? '';
        $shippingMethods = ShippingMethod::where('is_active', true)->orderBy('cost')->get();
        $cartController = new CartController();
        $cartData = $cartController->index()->getData();

        return view('checkout.index', [
            'cartDetails' => $cartData['cartDetails'],
            'subtotal' => $cartData['total'],
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
            'email' => $email,
            'phone' => $phone,
            'shippingMethods' => $shippingMethods
        ]);
    }

    public function process(Request $request)
    {
        $validatedData = $request->validate([
            'shipping_address' => 'required|string|max:1000',
            'billing_address' => 'nullable|string|max:1000',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:cash_on_delivery,stripe',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'notes' => 'nullable|string|max:1000',
            'stripe_payment_method_id' => 'nullable|string',
        ]);

        $cartItems = Session::get('cart', []);
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $orderItemsData = [];
            foreach ($cartItems as $identifier => $sessionItem) {
                $product = Product::find($sessionItem['product_id']);
                if (!$product) throw new \Exception("Product not found in cart.");

                $price = $product->price;
                $stock = $product->stock_quantity;
                $variant = null;

                if (!empty($sessionItem['product_variant_id'])) {
                    $variant = ProductVariant::find($sessionItem['product_variant_id']);
                    if (!$variant || $variant->stock_quantity < $sessionItem['quantity']) {
                        throw new \Exception("Product variant {$product->name} is out of stock.");
                    }
                    $price = $variant->price ?? $price;
                } elseif ($product->stock_quantity < $sessionItem['quantity']) {
                    throw new \Exception("Product {$product->name} is out of stock.");
                }

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id ?? null,
                    'quantity' => $sessionItem['quantity'],
                    'price' => $price,
                    'item_model' => $variant ?? $product
                ];
                $subtotal += $price * $sessionItem['quantity'];
            }

            $shippingMethod = ShippingMethod::findOrFail($validatedData['shipping_method_id']);
            $finalTotalAmount = $subtotal + $shippingMethod->cost;

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $finalTotalAmount,
                'shipping_cost' => $shippingMethod->cost,
                'shipping_method_id' => $shippingMethod->id,
                'order_status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address' => $validatedData['shipping_address'],
                'billing_address' => $validatedData['billing_address'] ?? $validatedData['shipping_address'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'],
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->orderItems()->create($itemData);
                $itemData['item_model']->decrement('stock_quantity', $itemData['quantity']);
            }

            $order->payment_status = 'pending';
            $order->save();

            DB::commit();
            Session::forget('cart');

            return redirect()->route('checkout.confirmation', $order)->with('success', 'Your order has been placed!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Processing Failed: ' . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'An error occurred while placing your order. Please try again. ' . $e->getMessage());
        }
    }

    public function confirmation(Order $order)
    {
        if (Auth::check() && $order->user_id !== Auth::id()) {
            abort(403);
        }
        
        $order->load('orderItems.product', 'orderItems.variant.attributeValues.attribute');

        return view('checkout.confirmation', compact('order'));
    }
}