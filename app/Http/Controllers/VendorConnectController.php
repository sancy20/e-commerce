<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe; // Import Stripe class
use Stripe\Account; // Import Stripe Account class
use Stripe\AccountLink; // Import Stripe AccountLink class
use App\Models\User; // To notify admins if needed

class VendorConnectController extends Controller
{
    public function __construct()
    {
        // Set Stripe API key from .env (STRIPE_SECRET)
        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Initiate the Stripe Connect onboarding process for a vendor.
     */
    public function onboard(Request $request)
    {
        $user = Auth::user();

        // Only approved vendors who are not already fully connected can onboard
        if (!$user->isVendor()) {
            return redirect()->route('vendor.dashboard')->with('error', 'Only approved vendors can connect Stripe.');
        }
        if ($user->stripe_connect_id && $user->payouts_enabled) {
            return redirect()->route('vendor.dashboard')->with('info', 'Your Stripe account is already connected and active.');
        }

        try {
            // 1. Create a Stripe Account (if not already created for this vendor)
            if (!$user->stripe_connect_id) {
                $account = Account::create([
                    'type' => 'standard',
                    'country' => 'US', // Set to your marketplace's default country
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'business_type' => 'individual', // Or 'company' if collecting business info
                    'metadata' => [
                        'user_id' => $user->id,
                    ],
                ]);
                $user->stripe_connect_id = $account->id;
                $user->save();
                Log::info('Stripe Connect: New Standard Account created for user ID ' . $user->id . ' Account ID: ' . $account->id);
            } else {
                $account = Account::retrieve($user->stripe_connect_id);
                Log::info('Stripe Connect: Retrieved existing account for user ID ' . $user->id . ' Account ID: ' . $account->id);
            }

            // 2. Create an Account Link to send the user to Stripe's onboarding flow
            $accountLink = AccountLink::create([
                'account' => $user->stripe_connect_id,
                'refresh_url' => route('vendor.stripe_connect.refresh'), // URL if the link expires
                'return_url' => route('vendor.stripe_connect.return'),   // URL after onboarding completion
                'type' => 'account_onboarding',
            ]);
            Log::info('Stripe Connect: Account Link created for user ID ' . $user->id . ' URL: ' . $accountLink->url);

            // Redirect the user to Stripe's onboarding URL
            return redirect()->away($accountLink->url);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe Connect Onboarding Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('vendor.dashboard')->with('error', 'Failed to connect Stripe. ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General Onboarding Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('vendor.dashboard')->with('error', 'An unexpected error occurred during Stripe onboarding.');
        }
    }

    /**
     * Handle return from Stripe after onboarding (success or incomplete).
     * This URL is hit even if onboarding wasn't fully completed.
     */
    public function returnFromStripe(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->stripe_connect_id) {
            return redirect()->route('vendor.dashboard')->with('error', 'Stripe connection failed: User not logged in or account not found.');
        }

        try {
            // Retrieve the connected account to check its status
            $account = Account::retrieve($user->stripe_connect_id);

            // Update user's payouts_enabled and charges_enabled status
            $user->payouts_enabled = $account->payouts_enabled;
            $user->charges_enabled = $account->charges_enabled;
            $user->save();

            if ($account->payouts_enabled) {
                Log::info('Stripe Connect: User ID ' . $user->id . ' successfully connected and payouts enabled.');
                return redirect()->route('vendor.dashboard')->with('success', 'Your Stripe account has been successfully connected!');
            } else {
                Log::warning('Stripe Connect: User ID ' . $user->id . ' returned from onboarding, but payouts not yet enabled. Requirements: ' . json_encode($account->requirements));
                return redirect()->route('vendor.dashboard')->with('info', 'Your Stripe account is connected, but requires more information to enable payouts. Please check your Stripe dashboard.');
            }

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe Connect Return Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('vendor.dashboard')->with('error', 'Failed to retrieve Stripe account status. Please try again.');
        }
    }

    /**
     * Handle refresh from Stripe (if onboarding session expired).
     */
    public function refreshFromStripe(Request $request)
    {
        // Simply redirect them back to the onboarding initiation to get a new link
        return redirect()->route('vendor.stripe_connect.onboard');
    }
}