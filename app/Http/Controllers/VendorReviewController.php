<?php

namespace App\Http\Controllers;

use App\Models\Review; // Import Review model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // For logging

class VendorReviewController extends Controller
{
    /**
     * Display a listing of reviews for the authenticated vendor's products.
     */
    public function index()
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@index initiated for vendor ID: ' . $vendorId);

        // Fetch reviews where the review's product belongs to the authenticated vendor
        $reviews = Review::whereHas('product', function ($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->with(['user', 'product']) // Eager load reviewer and product details
                        ->orderBy('created_at', 'desc')
                        ->paginate(15); // Paginate for large datasets

        return view('vendor.reviews.index', compact('reviews'));
    }

    /**
     * Display the specified review, ensuring it belongs to the vendor's product.
     */
    public function show(Review $review)
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@show initiated for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId);

        // Ensure the review's product belongs to the authenticated vendor
        if ($review->product->vendor_id !== $vendorId) {
            Log::warning('Unauthorized access attempt to review ID: ' . $review->id . ' by vendor ID: ' . $vendorId . ' (product not owned).');
            abort(403, 'Unauthorized access: This review is not for one of your products.');
        }

        $review->load('user', 'product'); // Eager load reviewer and product details

        return view('vendor.reviews.show', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@update (reply) initiated for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId);

        // Ensure the review's product belongs to the authenticated vendor
        if ($review->product->vendor_id !== $vendorId) {
            Log::warning('Unauthorized reply attempt for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId . ' (product not owned).');
            abort(403, 'Unauthorized action: This review is not for one of your products.');
        }

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        // Prevent replying to already replied or unapproved reviews if desired
        if (!$review->is_approved) {
             return redirect()->back()->with('error', 'Cannot reply to an unapproved review.');
        }
        if ($review->vendor_reply) {
            // If existing reply, ensure vendor can edit their own reply
             // return redirect()->back()->with('error', 'You have already replied to this review.');
        }

        try {
            $review->update([
                'vendor_reply' => $request->vendor_reply,
                'replied_at' => Carbon::now(),
            ]);
            Log::info('Review ID: ' . $review->id . ' replied to by vendor ID: ' . $vendorId);

            // Optional: Notify customer that vendor has replied to their review
            // Mail::to($review->user->email)->send(new VendorReplyNotificationMail($review));

            return redirect()->route('vendor.reviews.show', $review->id)
                             ->with('success', 'Your reply has been saved successfully!');

        } catch (\Exception $e) {
            Log::error('Error saving vendor reply for review ID: ' . $review->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'An error occurred while saving your reply. Please try again.');
        }
    }

    // Vendors will not create, edit (approval), or destroy reviews directly from here.
    // Those actions are typically for the main platform administrator.
}