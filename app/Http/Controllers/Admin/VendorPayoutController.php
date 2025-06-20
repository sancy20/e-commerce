<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VendorPayoutController extends Controller
{
    public function index()
    {
        $vendors = User::where('vendor_status', 'approved_vendor')
                       ->where('is_admin', false)
                       ->orderBy('name')
                       ->get();

        $recentPayouts = VendorPayout::with('vendor')
                                      ->orderBy('paid_at', 'desc')
                                      ->take(10)
                                      ->get();

        return view('admin.vendor_payouts.index', compact('vendors', 'recentPayouts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vendor = User::find($request->vendor_id);

        if ($vendor->getOutstandingPayoutAmount() < $request->amount) {
            return redirect()->back()->with('error', 'Cannot pay more than the outstanding amount for this vendor.');
        }

        try {
            VendorPayout::create([
                'vendor_id' => $request->vendor_id,
                'amount' => $request->amount,
                'status' => 'completed',
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
}