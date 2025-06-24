<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Carbon\Carbon; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorReviewController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@index initiated for vendor ID: ' . $vendorId);

        $reviews = Review::whereHas('product', function ($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->with(['user', 'product'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('vendor.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@show initiated for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId);


        if ($review->product->vendor_id !== $vendorId) {
            Log::warning('Unauthorized access attempt to review ID: ' . $review->id . ' by vendor ID: ' . $vendorId . ' (product not owned).');
            abort(403, 'Unauthorized access: This review is not for one of your products.');
        }

        $review->load('user', 'product');

        return view('vendor.reviews.show', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $vendorId = Auth::id();
        Log::info('VendorReviewController@update (reply) initiated for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId);
        if ($review->product->vendor_id !== $vendorId) {
            Log::warning('Unauthorized reply attempt for review ID: ' . $review->id . ' by vendor ID: ' . $vendorId . ' (product not owned).');
            abort(403, 'Unauthorized action: This review is not for one of your products.');
        }

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        if (!$review->is_approved) {
             return redirect()->back()->with('error', 'Cannot reply to an unapproved review.');
        }
        if ($review->vendor_reply) {
        }

        try {
            $review->update([
                'vendor_reply' => $request->vendor_reply,
                'replied_at' => Carbon::now(),
            ]);
            Log::info('Review ID: ' . $review->id . ' replied to by vendor ID: ' . $vendorId);

            return redirect()->route('vendor.reviews.show', $review->id)
                             ->with('success', 'Your reply has been saved successfully!');

        } catch (\Exception $e) {
            Log::error('Error saving vendor reply for review ID: ' . $review->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'An error occurred while saving your reply. Please try again.');
        }
    }
}