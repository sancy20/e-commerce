<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Inquiry $inquiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = 'New inquiry from ' . ($this->inquiry->sender->name ?? 'Guest') . ': "' . \Illuminate\Support\Str::limit($this->inquiry->subject, 50) . '"';
        $url = '';
        $icon = 'fa-envelope';

        // Determine redirect URL based on recipient type
        if ($notifiable->isAdmin()) {
            // Link to admin inbox or specific inquiry details if you build it
            $url = route('admin.dashboard'); // For now, link to general admin dashboard
        } elseif ($notifiable->isVendor()) {
            // Link to vendor inbox/inquiry details if you build it
            $url = route('vendor.dashboard'); // For now, link to general vendor dashboard
        }

        return [
            'type' => 'new_inquiry',
            'inquiry_id' => $this->inquiry->id,
            'sender_name' => $this->inquiry->sender->name ?? 'Guest',
            'subject' => $this->inquiry->subject,
            'message' => $message,
            'url' => $url,
            'icon' => $icon,
        ];
    }
}