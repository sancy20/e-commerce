<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // For timestamp
use Illuminate\Support\Facades\Mail; // For notifications
use App\Mail\VendorUpgradeRequestMail; // (Will create this Mailable later)
use App\Models\User; // To find admin
use App\Notifications\TierUpgradeRequestNotification;
use Illuminate\Support\Facades\Log;

class VendorUpgradeController extends Controller
{
    /**
     * Show the form for a vendor to request a tier upgrade.
     */
    public function showRequestForm()
    {
        $user = Auth::user();

        // Flags to pass to the view
        $hasPendingRequest = $user->hasPendingUpgradeRequest();
        $isHighestTier = $user->isDiamondVendor();
        $availableTiers = [];

        // Only calculate available tiers if eligible for upgrade
        if (!$isHighestTier && !$hasPendingRequest) {
            $currentTier = $user->vendor_tier;

            if ($currentTier === 'Silver') {
                $availableTiers['Gold'] = 'Gold';
                $availableTiers['Diamond'] = 'Diamond';
            } elseif ($currentTier === 'Gold') {
                $availableTiers['Diamond'] = 'Diamond';
            }
        }

        return view('vendor.upgrade_request.form', compact('user', 'hasPendingRequest', 'isHighestTier', 'availableTiers'));
    }

    /**
     * Handle the submission of the vendor upgrade request.
     * (This method's logic does not change from before)
     */
    public function submitRequest(Request $request)
    {
        $user = Auth::user();

        // Server-side checks: Only approved vendors, no pending requests, not highest tier
        if (!$user->isVendor() || $user->hasPendingUpgradeRequest() || $user->isDiamondVendor()) {
            return redirect()->back()->with('error', 'Invalid request: You cannot submit an upgrade request at this time.');
        }

        $availableTiers = [];
        $currentTier = $user->vendor_tier;
        if ($currentTier === 'Silver') {
            $availableTiers = ['Gold', 'Diamond'];
        } elseif ($currentTier === 'Gold') {
            $availableTiers = ['Diamond'];
        }

        $request->validate([
            'requested_tier' => 'required|string|in:' . implode(',', $availableTiers),
            'reason' => 'nullable|string|max:1000',
        ]);

        $user->upgrade_request_status = 'pending_upgrade';
        $user->requested_vendor_tier = $request->requested_tier;
        $user->upgrade_requested_at = Carbon::now();
        $user->save();

        $adminUsers = User::where('is_admin', true)->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new TierUpgradeRequestNotification($user, $request->requested_tier));
        }
        Log::info('Vendor upgrade request notification sent to admin for user ID: ' . $user->id);

        return redirect()->route('vendor.dashboard')->with('success', 'Your upgrade request to ' . $request->requested_tier . ' tier has been submitted and is awaiting review.');
    }
}