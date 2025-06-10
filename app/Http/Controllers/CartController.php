<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index()
    {
        $cartItems = Session::get('cart', []);
        $total = 0;
        $cartDetails = []; // This will now hold the prepared cart items from session
    
        foreach ($cartItems as $itemIdentifier => $item) {
            // Recalculate subtotal using current item details stored in session
            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
    
            $cartDetails[] = [
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['is_variant'] ? $item['product_variant_id'] : null,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'image' => $item['image'],
                'sku' => $item['sku'],
                'is_variant' => $item['is_variant'],
                'subtotal' => $subtotal,
                'item_identifier' => $itemIdentifier // Pass this for update/remove forms
            ];
        }
    
        // Re-check stock before rendering cart (optional but good for robustness)
        // This is a more complex check, typically done at checkout to prevent race conditions.
        // For simplicity, we'll rely on stock checks in add/update/process.
    
        return view('cart.index', compact('cartDetails', 'total'));
    }


    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id', // New validation for variant
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $variant = null;
        $itemIdentifier = $product->id; // Default identifier is product ID
        $itemDetails = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float)$product->price,
            'image' => $product->image,
            'sku' => $product->sku,
            'is_variant' => false,
        ];
        $availableStock = $product->stock_quantity;


        // If a variant is selected
        if ($request->filled('product_variant_id')) {
            $variant = ProductVariant::find($request->product_variant_id);

            if (!$variant || $variant->product_id !== $product->id) {
                return redirect()->back()->with('error', 'Invalid product variant selected!');
            }

            $itemIdentifier = 'variant_' . $variant->id; // Use variant ID as identifier in cart session
            $itemDetails = [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'name' => $product->name . ' (' . $variant->variant_name . ')', // Product Name + Variant Name
                'price' => (float)$variant->price ?? (float)$product->price, // Use variant price, fallback to product price
                'image' => $variant->image ?? $product->image, // Use variant image, fallback to product image
                'sku' => $variant->sku ?? $product->sku,
                'is_variant' => true,
            ];
            $availableStock = $variant->stock_quantity;
        }

        if ($availableStock < $request->quantity) {
            return redirect()->back()->with('error', 'Not enough stock for this selection! Only ' . $availableStock . ' available.');
        }

        $cart = Session::get('cart', []);

        // Initialize or update quantity
        $currentQuantityInCart = $cart[$itemIdentifier]['quantity'] ?? 0;
        $newQuantity = $currentQuantityInCart + $request->quantity;

        if ($newQuantity > $availableStock) {
            $newQuantity = $availableStock; // Cap at available stock
            $itemDetails['quantity'] = $newQuantity;
            $cart[$itemIdentifier] = $itemDetails; // Update with capped quantity
            Session::put('cart', $cart);
            return redirect()->back()->with('error', 'Maximum available stock for ' . $itemDetails['name'] . ' added to cart. No more available.');
        }

        $itemDetails['quantity'] = $newQuantity;
        $cart[$itemIdentifier] = $itemDetails; // Store/Update the item with its details

        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', $itemDetails['name'] . ' added to cart!');
    }

    /**
     * Update product/variant quantity in the cart.
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_identifier' => 'required|string', // This will be product_id or variant_X
            'quantity' => 'required|integer|min:0',
        ]);

        $itemIdentifier = $request->item_identifier;
        $cart = Session::get('cart', []);

        if (!isset($cart[$itemIdentifier])) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }

        // Determine if it's a variant or base product
        $isVariant = str_starts_with($itemIdentifier, 'variant_');
        $id = $isVariant ? (int)str_replace('variant_', '', $itemIdentifier) : (int)$itemIdentifier;

        $product = null;
        $variant = null;
        $availableStock = 0;
        $itemName = $cart[$itemIdentifier]['name'] ?? 'Item'; // For messages

        if ($isVariant) {
            $variant = ProductVariant::find($id);
            if ($variant) {
                $product = $variant->product; // Get parent product
                $availableStock = $variant->stock_quantity;
            }
        } else {
            $product = Product::find($id);
            if ($product) {
                $availableStock = $product->stock_quantity;
            }
        }

        if (!$product && !$variant) {
             // Item (product or variant) no longer exists in DB, remove from cart
            unset($cart[$itemIdentifier]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('error', $itemName . ' is no longer available and has been removed from your cart.');
        }


        if ($request->quantity == 0) {
            unset($cart[$itemIdentifier]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', $itemName . ' removed from cart.');
        } elseif ($request->quantity > $availableStock) {
            return redirect()->back()->with('error', 'Not enough stock for ' . $itemName . '! Max quantity is ' . $availableStock . '.');
        } else {
            // Update quantity and refresh details (price, name, image)
            $cart[$itemIdentifier]['quantity'] = $request->quantity;
            if ($isVariant && $variant) {
                $cart[$itemIdentifier]['name'] = $product->name . ' (' . $variant->variant_name . ')';
                $cart[$itemIdentifier]['price'] = (float)$variant->price ?? (float)$product->price;
                $cart[$itemIdentifier]['image'] = $variant->image ?? $product->image;
                $cart[$itemIdentifier]['sku'] = $variant->sku ?? $product->sku;
            } elseif ($product) { // Base product
                $cart[$itemIdentifier]['name'] = $product->name;
                $cart[$itemIdentifier]['price'] = (float)$product->price;
                $cart[$itemIdentifier]['image'] = $product->image;
                $cart[$itemIdentifier]['sku'] = $product->sku;
            }

            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
        }
    }

    /**
     * Remove a product from the cart.
     */
    public function remove($productId)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $productName = $cart[$productId]['name'] ?? 'Item'; // Use null coalescing for safety
            unset($cart[$productId]);
            Session::put('cart', $cart);
            return redirect()->route('cart.index')->with('success', $productName . ' removed from cart.');
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

    /**
     * Helper function to calculate total amount.
     * Note: This function itself should not cause 'price' error if 'add' is fixed,
     * but it's good practice to ensure 'price' is present in $item
     */
    protected function calculateCartTotal(array $cartItems): float
    {
        $total = 0;
        foreach ($cartItems as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0); // Use null coalescing for safety
        }
        return $total;
    }
}