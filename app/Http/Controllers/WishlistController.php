<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Auth::user()->wishlists()->with(['product.variants.attributeValues', 'productVariant.attributeValues.attribute'])->get();
        return view('wishlist.index', compact('wishlists'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = Product::find($request->product_id);

        if ($product->hasVariants() && !$request->filled('product_variant_id')) {
            return redirect()->back()->with('error', 'Please select a variant for this product to add to wishlist.');
        }

        if ($request->filled('product_variant_id')) {
            $variant = ProductVariant::find($request->product_variant_id);
            if (!$variant || $variant->product_id !== $product->id) {
                return redirect()->back()->with('error', 'Invalid product variant selected!');
            }
            $product_variant_id = $variant->id;
            $itemName = $product->name . ' (' . $variant->variant_name . ')';
        } else {
            $product_variant_id = null;
            $itemName = $product->name;
        }

        $existing = Wishlist::where('user_id', Auth::id())
                            ->where('product_id', $product->id)
                            ->where('product_variant_id', $product_variant_id)
                            ->first();

        if ($existing) {
            return redirect()->back()->with('info', $itemName . ' is already in your wishlist.');
        }

        Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'product_variant_id' => $product_variant_id,
        ]);

        return redirect()->back()->with('success', $itemName . ' added to your wishlist!');
    }

    public function remove(Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $itemName = $wishlist->product->name . ($wishlist->productVariant ? ' (' . $wishlist->productVariant->variant_name . ')' : '');
        $wishlist->delete();

        return redirect()->back()->with('success', $itemName . ' removed from wishlist.');
    }

    public function moveToCart(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== Auth::id()) {
            abort(403);
        }

        $product = $wishlist->product;
        $variant = $wishlist->productVariant;
        $itemName = $product->name . ($variant ? ' (' . $variant->variant_name . ')' : '');
        $itemStock = $variant ? $variant->stock_quantity : $product->stock_quantity;

        if ($itemStock <= 0) {
            return redirect()->back()->with('error', $itemName . ' is out of stock and cannot be moved to cart.');
        }

        $cart = Session::get('cart', []);
        $itemIdentifier = $variant ? 'variant_' . $variant->id : $product->id;

        $currentQuantityInCart = $cart[$itemIdentifier]['quantity'] ?? 0;
        if ($currentQuantityInCart >= $itemStock) {
             return redirect()->back()->with('info', $itemName . ' is already at max stock in your cart.');
        }


        $cart[$itemIdentifier] = [
            'product_id' => $product->id,
            'product_variant_id' => $variant ? $variant->id : null,
            'name' => $itemName,
            'price' => (float)($variant->price ?? $product->price),
            'image' => $variant->image ?? $product->image,
            'sku' => $variant->sku ?? $product->sku,
            'quantity' => 1,
            'is_variant' => (bool)$variant,
        ];

        Session::put('cart', $cart);

        $wishlist->delete();

        return redirect()->route('cart.index')->with('success', $itemName . ' moved to cart!');
    }
}