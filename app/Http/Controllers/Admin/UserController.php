<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorApplicationStatusMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        Log::info('Admin/UserController@update initiated for user ID: ' . $user->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            // 'is_vendor' => 'boolean',
            'is_admin' => 'boolean', 
            'vendor_tier' => 'required_if:is_vendor,on|string|in:Silver,Gold,Diamond',
            'commission_rate' => 'required_if:is_vendor,on|numeric|min:0|max:1',
        ]);
        Log::info('Admin/UserController@update validation passed for user ID: ' . $user->id);

        $oldVendorStatus = $user->vendor_status;
        $newVendorStatus = $request->has('is_vendor') ? 'approved_vendor' : 'customer';

        $oldAdminStatus = $user->is_admin;
        $newAdminStatus = $request->has('is_admin');


        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
            'business_name' => $request->business_name,
            'business_address' => $request->business_address,
            'business_description' => $request->business_description,
            'vendor_status' => $newVendorStatus,
            'is_admin' => $newAdminStatus,
            'vendor_tier' => $request->has('is_vendor') ? $request->vendor_tier : 'Customer',
            'commission_rate' => $request->has('is_vendor') ? $request->commission_rate : 0.0000,
        ]);
        Log::info('User ID: ' . $user->id . ' profile updated. New vendor_status: ' . $user->vendor_status . ', tier: ' . $user->vendor_tier . ', is_admin: ' . ($user->is_admin ? 'true' : 'false'));


        $upgradeRequestWasPending = ($user->getOriginal('upgrade_request_status') === 'pending_upgrade'); 
        $previouslyRequestedTier = $user->getOriginal('requested_vendor_tier'); // Get original value

        if ($upgradeRequestWasPending && $user->vendor_tier === $previouslyRequestedTier) {
            $user->update([ 
                'upgrade_request_status' => null,
                'requested_vendor_tier' => null,
                'upgrade_requested_at' => null,
            ]);
            Log::info('Vendor upgrade request fulfilled for user ID: ' . $user->id . ' to tier: ' . $user->vendor_tier . '. Request status cleared.');
        }


        if ($oldVendorStatus === 'pending_vendor' && $newVendorStatus === 'approved_vendor') {
            if ($user->email) {
                Mail::to($user->email)->send(new VendorApplicationStatusMail($user, 'approved', ''));
            }
        } elseif ($oldVendorStatus === 'pending_vendor' && $newVendorStatus === 'customer') {
            if ($user->email) {
                Mail::to($user->email)->send(new VendorApplicationStatusMail($user, 'rejected', 'Your application was not approved at this time.'));
            }
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (Auth::check() && Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own admin account.');
        }

        $user->delete();
        Log::info('User ID: ' . $user->id . ' deleted by admin.');
        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted successfully.');
    }
}