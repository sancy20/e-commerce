<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{

    public function index()
    {
        $sessionCart = session('cart', []);
        $cartDetails = [];
        $total = 0;

        foreach ($sessionCart as $identifier => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $price = $product->price;
            $image = $product->image;
            $sku = $product->sku;
            $variantName = $product->variant_name; 
            
            if (!empty($item['product_variant_id'])) {
                $variant = ProductVariant::find($item['product_variant_id']);
                if ($variant) {
                    $variantName = $variant->variant_name;
                    $price = $variant->price ?? $price;
                    $image = $variant->image ?? $image;
                    $sku = $variant->sku ?? $sku;
                }
            }

            $subtotal = $price * $item['quantity'];
            $total += $subtotal;

            $cartDetails[] = [
                'item_identifier' => $identifier,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $item['quantity'],
                'image' => $image,
                'sku' => $sku,
                'variant_name' => $variantName,
                'subtotal' => $subtotal,
            ];
        }

        return view('cart.index', compact('cartDetails', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int)$request->quantity;
        $cart = session()->get('cart', []);

        $itemIdentifier = (string)$product->id;
        $maxStock = $product->stock_quantity;
        $itemData = [
            'product_id' => $product->id,
            'product_variant_id' => null
        ];

        if ($request->filled('product_variant_id')) {
            $variant = ProductVariant::findOrFail($request->product_variant_id);
            $itemIdentifier = $product->id . '_' . $variant->id;
            $itemData['product_variant_id'] = $variant->id;
            $maxStock = $variant->stock_quantity;
        }

        $currentQuantityInCart = $cart[$itemIdentifier]['quantity'] ?? 0;
        if ($maxStock < ($quantity + $currentQuantityInCart)) {
            return redirect()->back()->with('error', 'Not enough stock available. Only ' . $maxStock . ' left.');
        }
        
        $cart[$itemIdentifier] = $itemData + ['quantity' => $quantity + $currentQuantityInCart];
        session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', $product->name . ' added to cart!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_identifier' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $itemIdentifier = $request->item_identifier;
        $quantity = (int)$request->quantity;
        $cart = Session::get('cart', []);

        if (!isset($cart[$itemIdentifier])) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }

        if ($quantity == 0) {
            unset($cart[$itemIdentifier]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
        }

        // Check stock availability
        $ids = explode('_', $itemIdentifier);
        $productId = $ids[0];
        $variantId = $ids[1] ?? null;

        $availableStock = 0;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            $availableStock = $variant ? $variant->stock_quantity : 0;
        } else {
            $product = Product::find($productId);
            $availableStock = $product ? $product->stock_quantity : 0;
        }

        if ($quantity > $availableStock) {
            return redirect()->back()->with('error', 'Not enough stock! Maximum available quantity is ' . $availableStock . '.');
        }

        $cart[$itemIdentifier]['quantity'] = $quantity;
        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove($item_identifier)
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$item_identifier])) {
            unset($cart[$item_identifier]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
        }
        return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }
}