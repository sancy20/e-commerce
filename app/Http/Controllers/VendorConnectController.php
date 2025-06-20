<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\AccountLink;
use App\Models\User;

class VendorConnectController extends Controller
{
    public function __construct()
    {

        Stripe::setApiKey(config('cashier.secret'));
    }

    public function onboard(Request $request)
    {
        $user = Auth::user();

        if (!$user->isVendor()) {
            return redirect()->route('vendor.dashboard')->with('error', 'Only approved vendors can connect Stripe.');
        }
        if ($user->stripe_connect_id && $user->payouts_enabled) {
            return redirect()->route('vendor.dashboard')->with('info', 'Your Stripe account is already connected and active.');
        }

        try {
            if (!$user->stripe_connect_id) {
                $account = Account::create([
                    'type' => 'standard',
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'business_type' => 'individual',
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

            $accountLink = AccountLink::create([
                'account' => $user->stripe_connect_id,
                'refresh_url' => route('vendor.stripe_connect.refresh'),
                'return_url' => route('vendor.stripe_connect.return'),
                'type' => 'account_onboarding',
            ]);
            Log::info('Stripe Connect: Account Link created for user ID ' . $user->id . ' URL: ' . $accountLink->url);

            return redirect()->away($accountLink->url);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe Connect Onboarding Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('vendor.dashboard')->with('error', 'Failed to connect Stripe. ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General Onboarding Error for user ID ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('vendor.dashboard')->with('error', 'An unexpected error occurred during Stripe onboarding.');
        }
    }

    public function returnFromStripe(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->stripe_connect_id) {
            return redirect()->route('vendor.dashboard')->with('error', 'Stripe connection failed: User not logged in or account not found.');
        }

        try {
            $account = Account::retrieve($user->stripe_connect_id);


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

    public function refreshFromStripe(Request $request)
    {
        return redirect()->route('vendor.stripe_connect.onboard');
    }
}