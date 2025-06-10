<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorPayout; // Import VendorPayout model
use App\Models\User;         // Import User model
use Illuminate\Http\Request;
use Carbon\Carbon;            // For current timestamp
use Illuminate\Support\Facades\Log; // For logging

class VendorPayoutController extends Controller
{
    /**
     * Display a listing of vendor payouts (history) and outstanding balances.
     */
    public function index()
    {
        // Get all approved vendors with their outstanding balances
        $vendors = User::where('vendor_status', 'approved_vendor')
                       ->where('is_admin', false) // Optionally exclude admin users who are also vendors
                       ->orderBy('name')
                       ->get();

        // Get recent payout history
        $recentPayouts = VendorPayout::with('vendor')
                                      ->orderBy('paid_at', 'desc')
                                      ->take(10) // Show last 10 payouts
                                      ->get();

        return view('admin.vendor_payouts.index', compact('vendors', 'recentPayouts'));
    }

    /**
     * Store a newly created payout record in storage (marking an amount as paid).
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vendor = User::find($request->vendor_id);

        // Basic validation to prevent overpaying (optional but good)
        if ($vendor->getOutstandingPayoutAmount() < $request->amount) {
            return redirect()->back()->with('error', 'Cannot pay more than the outstanding amount for this vendor.');
        }

        try {
            VendorPayout::create([
                'vendor_id' => $request->vendor_id,
                'amount' => $request->amount,
                'status' => 'completed', // Assuming creation means it's completed
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'paid_at' => Carbon::now(),
            ]);

            Log::info('Vendor payout recorded: ' . $request->amount . ' to vendor ID: ' . $request->vendor_id);
            return redirect()->route('admin.dashboard')
                             ->with('success', 'Payout recorded successfully for ' . $vendor->name . '.');
        } catch (\Exception $e) {
            Log::error('Error recording vendor payout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to record payout: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payout record (for corrections). Use with caution.
     */
    public function destroy(VendorPayout $vendorPayout)
    {
        try {
            $vendorName = $vendorPayout->vendor->name ?? 'N/A';
            $vendorPayout->delete();
            Log::info('Vendor payout record deleted: ' . $vendorPayout->id . ' for vendor: ' . $vendorName);
            return redirect()->route('admin.dashboard')
                             ->with('success', 'Payout record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting vendor payout record: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete payout record: ' . $e->getMessage());
        }
    }

    // create, edit, show methods are excluded for simplicity of this resource
}