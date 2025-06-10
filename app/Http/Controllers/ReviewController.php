<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Review; // Import Review model
use Illuminate\Support\Facades\Auth; // To get the authenticated user

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if the user has already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already submitted a review for this product.');
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // Reviews are set to false by default, requiring admin approval
        ]);

        return redirect()->back()->with('success', 'Your review has been submitted successfully and is awaiting approval!');
    }
}