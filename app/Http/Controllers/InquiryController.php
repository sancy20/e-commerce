<?php

namespace App\Http\Controllers;

use App\Models\Inquiry; // Import Inquiry model
use App\Models\Product; // Import Product model
use App\Models\User;    // Import User model (for admin recipient)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewInquiryNotification; // Will create this Notification

class InquiryController extends Controller
{
    /**
     * Show the form to create a new inquiry.
     */
    public function create(Product $product = null)
    {
        return view('inquiries.create', compact('product'));
    }

    /**
     * Store a newly created inquiry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $sender = Auth::user();
        $recipient = null;

        if ($request->filled('product_id')) {
            $product = Product::find($request->product_id);
            if ($product && $product->vendor && $product->vendor->isVendor()) {
                $recipient = $product->vendor; // Send to product's vendor
            }
        }

        if (!$recipient) {
            $recipient = User::where('is_admin', true)->first();
            if (!$recipient) {
                Log::error('Inquiry: No admin found to receive inquiry.');
                return redirect()->back()->with('error', 'Could not send inquiry. No admin found.');
            }
        }

        try {
            $inquiry = Inquiry::create([
                'user_id' => $sender->id,
                'product_id' => $request->product_id,
                'recipient_id' => $recipient->id,
                'subject' => $request->subject,
                'message' => $request->message,
                'source_type' => 'general', // <--- FIX: Add this line to set the source type
            ]);
            Log::info('New inquiry created from user ID: ' . $sender->id . ' to recipient ID: ' . $recipient->id . ' Inquiry ID: ' . $inquiry->id);

            // Notify recipient (vendor or admin)
            $recipient->notify(new NewInquiryNotification($inquiry));

            return redirect()->route('dashboard.index')->with('success', 'Your inquiry has been sent!');
        } catch (\Exception $e) {
            Log::error('Error sending inquiry: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while sending your inquiry.');
        }
    }
}