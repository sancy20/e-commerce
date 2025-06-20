<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VendorApplicationStatusMail;
use App\Notifications\VendorApplicationNotification;
use Illuminate\Support\Facades\Log;

class VendorApplicationController extends Controller
{
    public function showApplicationForm()
    {
        $user = Auth::user();

        if ($user->isVendor() || $user->isPendingVendor()) {
            return redirect()->route('dashboard.index')->with('info', 'You have already applied or are an approved vendor.');
        }

        return view('vendor_application.create');
    }

    public function submitApplication(Request $request)
    {
        $applicantUser = Auth::user(); 
        Log::info('VendorApplicationController@submitApplication initiated for user ID: ' . $applicantUser->id);


        if ($applicantUser->isVendor() || $applicantUser->isPendingVendor()) {
            Log::warning('User ID ' . $applicantUser->id . ' attempted to apply as vendor but is already vendor/pending.');
            return redirect()->back()->with('error', 'You have already applied or are an approved vendor.');
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_address' => 'required|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            'agreement' => 'required|accepted',
        ]);

        $applicantUser->business_name = $request->business_name;
        $applicantUser->business_address = $request->business_address;
        $applicantUser->business_description = $request->business_description;
        $applicantUser->vendor_status = 'pending_vendor';
        $applicantUser->save();
        Log::info('User ID ' . $applicantUser->id . ' status updated to pending_vendor.');

        $adminUsers = User::where('is_admin', true)->get();
        if ($adminUsers->isEmpty()) {
            Log::warning('No admin users found to notify for new vendor application from user ID: ' . $applicantUser->id);
        } else {
            Log::info('Found ' . $adminUsers->count() . ' admin users to notify.');
        }

        foreach ($adminUsers as $admin) {
            try {
                $admin->notify(new VendorApplicationNotification($applicantUser, 'submitted'));
                Log::info('Vendor application DB notification sent to admin ID: ' . $admin->id . ' for user ID: ' . $applicantUser->id);

                // Optional: Send Email Notification to Admin (if desired)
                // (Ensure AdminNotificationMail is created and imported if uncommenting)
                /*
                if ($admin->email) {
                    $message = 'New vendor application from ' . $applicantUser->name;
                    $url = route('admin.users.edit', $applicantUser->id);
                    Mail::to($admin->email)->send(new AdminNotificationMail($message, $url, [
                        'Applicant Name' => $applicantUser->name,
                        'Applicant Email' => $applicantUser->email,
                        'Business Name' => $applicantUser->business_name,
                    ], 'New Vendor Application'));
                    Log::info('Vendor application email sent to admin ID: ' . $admin->id . ' for user ID: ' . $applicantUser->id);
                }
                */
            } catch (\Exception $notifyE) {
                Log::error('Error sending vendor application notification to admin ID: ' . $admin->id . ': ' . $notifyE->getMessage() . ' in ' . $notifyE->getFile() . ' on line ' . $notifyE->getLine());
            }
        }

        return redirect()->route('dashboard.index')->with('success', 'Your vendor application has been submitted and is awaiting admin review.');
    }
}