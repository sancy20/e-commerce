<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Inquiry $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = 'New inquiry from ' . ($this->inquiry->sender->name ?? 'Guest') . ': "' . Str::limit($this->inquiry->subject, 50) . '"';
        $url = '';
        $icon = 'fa-envelope';

        if ($notifiable->isAdmin()) {
            $url = route('admin.inquiries.show', $this->inquiry->id);
        } elseif ($notifiable->isVendor()) {
            $url = route('vendor.dashboard'); // Vendor doesn't have inquiry show page yet, link to dashboard
        }

        return [
            'type' => 'new_inquiry',
            'inquiry_id' => $this->inquiry->id,
            'sender_name' => $this->inquiry->sender->name ?? 'Guest',
            'subject' => $this->inquiry->subject,
            'message' => $message,
            'url' => $url,
            'icon' => $icon,
            'source_type' => $this->inquiry->source_type, // <--- This correctly takes source_type from the Inquiry model
        ];
    }
}