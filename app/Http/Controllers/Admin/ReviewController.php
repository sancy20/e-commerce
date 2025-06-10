<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review; // Import Review model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request) // Added Request to filter
    {
        // Fetch all reviews, eager load user and product, order by creation date
        // Prioritize unapproved reviews at the top
        $query = Review::with('user', 'product')
                        ->orderBy('is_approved', 'asc') // Unapproved first
                        ->orderBy('created_at', 'desc');

        // Optional: Filter by approval status if 'is_approved' parameter is present
        if ($request->has('is_approved') && in_array($request->is_approved, ['true', 'false'])) {
            $query->where('is_approved', $request->is_approved === 'true' ? true : false);
        }

        $reviews = $query->paginate(15)->withQueryString(); // Paginate and append query string

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review)
    {
        $review->load('user', 'product'); // Eager load relationships
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Show the form for editing the specified review (primarily for approval).
     */
    public function edit(Review $review)
    {
        $review->load('user', 'product'); // Eager load relationships
        return view('admin.reviews.edit', compact('review'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            // 'is_approved' => 'boolean', // This validation rule caused issues before, rely on has()
        ]);

        // Determine is_approved status based on checkbox presence
        $newIsApproved = $request->has('is_approved');

        try {
            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => $newIsApproved, // Handle checkbox state
            ]);
            Log::info('Review ID: ' . $review->id . ' updated by admin. New approved status: ' . ($newIsApproved ? 'true' : 'false'));

            // Optional: Notify customer if their review status changed
            // You'd need a specific Mailable for this (e.g., ReviewStatusUpdatedMail)
            // if ($review->user && $review->user->email) {
            //     Mail::to($review->user->email)->send(new ReviewStatusUpdatedMail($review));
            // }

            return redirect()->route('admin.dashboard') // Redirect to admin dashboard
                             ->with('success', 'Review updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating review ID: ' . $review->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'Failed to update review. An internal error occurred.');
        }
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        try {
            $review->delete();
            Log::info('Review ID: ' . $review->id . ' deleted by admin.');
            return redirect()->route('admin.dashboard') // Redirect to admin dashboard
                             ->with('success', 'Review deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting review ID: ' . $review->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'Failed to delete review. An internal error occurred.');
        }
    }

    // `create` and `store` methods are not needed as reviews are submitted by users.
}