<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminInquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inquiry::with(['sender', 'product', 'recipient'])
                        ->orderBy('is_read', 'asc')
                        ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('is_read') && in_array($request->is_read, ['true', 'false'])) {
            $query->where('is_read', $request->is_read === 'true');
        }

        // Filter by source type
        if ($request->has('source_type') && in_array($request->source_type, ['general', 'email'])) {
            $query->where('source_type', $request->source_type);
        }

        $inquiries = $query->paginate(15)->withQueryString();
        $lastEmailSent = Cache::get('last_email_sent_debug');

        return view('admin.inquiries.index', compact('inquiries'));
    }

    public function show(Inquiry $inquiry)
    {
        $inquiry->load(['sender', 'product.vendor', 'recipient', 'replies.sender']);

        // Mark as read when admin views it
        if (!$inquiry->is_read) {
            $inquiry->is_read = true;
            $inquiry->save();
            Log::info('Inquiry ID: ' . $inquiry->id . ' marked as read upon viewing by admin.');
        }

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function markAsRead(Inquiry $inquiry)
    {
        if (!$inquiry->is_read) {
            $inquiry->is_read = true;
            $inquiry->save();
            Log::info('Inquiry ID: ' . $inquiry->id . ' marked as read via direct action.');
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'already read']);
    }

    public function destroy(Inquiry $inquiry)
    {
        try {
            $inquiry->delete();
            Log::info('Inquiry ID: ' . $inquiry->id . ' deleted by admin.');
            return redirect()->route('admin.inquiries.index')
                             ->with('success', 'Inquiry deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting inquiry ID: ' . $inquiry->id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete inquiry.');
        }
    }
}