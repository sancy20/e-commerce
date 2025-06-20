<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TierUpgradeRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $vendor;
    public string $requestedTier;

    public function __construct(User $vendor, string $requestedTier)
    {
        $this->vendor = $vendor;
        $this->requestedTier = $requestedTier;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->vendor->name . ' (' . $this->vendor->email . ') has requested an upgrade to ' . $this->requestedTier . ' tier.';
        $url = route('admin.users.edit', $this->vendor->id);
        $icon = 'fa-level-up-alt';

        return [
            'type' => 'tier_upgrade_request',
            'user_id' => $this->vendor->id,
            'user_name' => $this->vendor->name,
            'requested_tier' => $this->requestedTier,
            'message' => $message,
            'url' => $url,
            'icon' => $icon,
            'source_type' => 'upgrade_request', // <--- ENSURE THIS IS PRESENT AND CORRECT
        ];
    }
}