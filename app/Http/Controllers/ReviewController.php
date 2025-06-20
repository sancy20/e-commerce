<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewReviewNotification;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $existingReview = $product->reviews()->where('user_id', Auth::id())->exists();
        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already submitted a review for this product.');
        }

        $review = $product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true,
        ]);

        $admin = \App\Models\User::where('is_admin', true)->first();
        if ($admin) {
            $admin->notify(new NewReviewNotification($review, 'admin'));
        }
        if ($product->vendor) {
            $product->vendor->notify(new NewReviewNotification($review, 'vendor'));
        }

        return redirect()->back()->with('success', 'Thank you for your review!');
    }
}