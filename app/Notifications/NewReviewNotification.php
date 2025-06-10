<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Review $review;
    public string $recipientType; // 'admin' or 'vendor'

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review, string $recipientType = 'admin')
    {
        $this->review = $review;
        $this->recipientType = $recipientType;
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
        $message = '';
        $url = '';
        $icon = 'fa-star';

        if ($this->recipientType === 'admin') {
            $message = 'New review (rating: ' . $this->review->rating . ') for product "' . ($this->review->product->name ?? 'N/A') . '" by ' . ($this->review->user->name ?? 'Guest') . '.';
            $url = route('admin.reviews.edit', $this->review->id); // Link to admin review moderation
        } elseif ($this->recipientType === 'vendor') {
            $message = 'New review (rating: ' . $this->review->rating . ') received for your product "' . ($this->review->product->name ?? 'N/A') . '".';
            $url = route('vendor.reviews.show', $this->review->id); // Link to vendor's review view
        }

        return [
            'type' => 'new_review',
            'review_id' => $this->review->id,
            'product_id' => $this->review->product->id ?? null,
            'product_name' => $this->review->product->name ?? 'N/A',
            'rating' => $this->review->rating,
            'message' => $message,
            'url' => $url,
            'icon' => $icon,
        ];
    }
}