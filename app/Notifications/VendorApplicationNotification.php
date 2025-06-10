<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log; // Add Log facade for debugging

class VendorApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $applicant;
    public string $status;

    public function __construct(User $applicant, string $status = 'submitted')
    {
        $this->applicant = $applicant;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        Log::info('VendorApplicationNotification: toArray method called. Status: ' . $this->status); // <--- ADD THIS LOG
        // dd([$this->applicant->toArray(), $this->status, $notifiable->toArray()]); // <-- UNCOMMENT FOR IMMEDIATE DEBUGGING

        $message = '';
        $url = '';
        $icon = '';

        if ($this->status === 'submitted') {
            $message = 'New vendor application from ' . $this->applicant->name . ' (' . $this->applicant->email . ').';
            $url = route('admin.users.edit', $this->applicant->id);
            $icon = 'fa-user-plus';
        } elseif ($this->status === 'approved') {
            $message = 'Your vendor application has been approved!';
            $url = route('vendor.dashboard');
            $icon = 'fa-check-circle';
        } elseif ($this->status === 'rejected') {
            $message = 'Your vendor application has been rejected.';
            $url = route('dashboard.index');
            $icon = 'fa-times-circle';
        }

        $notificationData = [
            'type' => 'vendor_application',
            'user_id' => $this->applicant->id,
            'user_name' => $this->applicant->name,
            'message' => $message,
            'url' => $url,
            'status' => $this->status,
            'icon' => $icon,
        ];

        Log::info('VendorApplicationNotification: Data prepared for DB: ' . json_encode($notificationData)); // <--- ADD THIS LOG
        return $notificationData;
    }
}