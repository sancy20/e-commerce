<?php

namespace App\Http\Controllers;

use App\Models\Inquiry; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerInquiryReplyMail;

class VendorInquiryController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::id();
        Log::info('VendorInquiryController@index initiated for vendor ID: ' . $vendorId);

        $query = Inquiry::where('recipient_id', $vendorId)
                        ->with(['sender', 'product'])
                        ->orderBy('is_read', 'asc')
                        ->orderBy('created_at', 'desc');

        if ($request->has('is_read') && in_array($request->is_read, ['true', 'false'])) {
            $query->where('is_read', $request->is_read === 'true');
        }

        if ($request->has('source_type') && in_array($request->source_type, ['general', 'email'])) {
            $query->where('source_type', $request->source_type);
        }

        $inquiries = $query->paginate(15)->withQueryString();

        return view('vendor.inquiries.index', compact('inquiries'));
    }

    public function show(Inquiry $inquiry)
    {
        $vendorId = Auth::id();
        Log::info('VendorInquiryController@show initiated for inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId);

        if ($inquiry->recipient_id !== $vendorId) {
            Log::warning('Unauthorized access attempt to inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId . ' (not recipient).');
            abort(403, 'Unauthorized access: This inquiry is not for you.');
        }

        $inquiry->load(['sender', 'product.vendor', 'replies.sender']);

        if (!$inquiry->is_read) {
            $inquiry->is_read = true;
            $inquiry->save();
            Log::info('Inquiry ID: ' . $inquiry->id . ' marked as read upon viewing by vendor.');
        }

        return view('vendor.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $vendorId = Auth::id();
        Log::info('VendorInquiryController@update (reply) initiated for inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId);

        if ($inquiry->recipient_id !== $vendorId) {
            Log::warning('Unauthorized reply attempt for inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId . ' (not recipient).');
            abort(403, 'Unauthorized action: This inquiry is not for you.');
        }

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        try {
            $inquiry->update([
                'vendor_reply' => $request->vendor_reply,
                'replied_at' => Carbon::now(),
                'is_read' => true,
            ]);
            Log::info('Inquiry ID: ' . $inquiry->id . ' replied to by vendor ID: ' . $vendorId);

            if ($inquiry->sender && $inquiry->sender->email) {
            }

            return redirect()->route('vendor.inquiries.show', $inquiry->id)
                             ->with('success', 'Your reply has been saved successfully!');

        } catch (\Exception $e) {
            Log::error('Error saving vendor reply for inquiry ID: ' . $inquiry->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'An error occurred while saving your reply. Please try again.');
        }
    }

    public function markAsRead(Inquiry $inquiry)
    {
        $vendorId = Auth::id();
        Log::info('VendorInquiryController@markAsRead initiated for inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId);

        if ($inquiry->recipient_id !== $vendorId) {
            Log::warning('Unauthorized mark-as-read attempt for inquiry ID: ' . $inquiry->id . ' by vendor ID: ' . $vendorId . ' (not recipient).');
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$inquiry->is_read) {
            $inquiry->is_read = true;
            $inquiry->save();
            Log::info('Inquiry ID: ' . $inquiry->id . ' marked as read by vendor ID: ' . $vendorId);
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'already read']);
    }
}