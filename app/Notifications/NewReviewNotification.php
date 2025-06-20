<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Review $review;
    public string $recipientType;

    public function __construct(Review $review, string $recipientType = 'admin')
    {
        $this->review = $review;
        $this->recipientType = $recipientType;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = '';
        $url = '';
        $icon = 'fa-star';

        if ($this->recipientType === 'admin') {
            $message = 'New review (rating: ' . $this->review->rating . ') for product "' . ($this->review->product->name ?? 'N/A') . '" by ' . ($this->review->user->name ?? 'Guest') . '.';
            $url = route('admin.reviews.edit', $this->review->id);
        } elseif ($this->recipientType === 'vendor') {
            $message = 'New review (rating: ' . $this->review->rating . ') received for your product "' . ($this->review->product->name ?? 'N/A') . '".';
            $url = route('vendor.reviews.show', $this->review->id);
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
            'source_type' => 'review', // <--- ENSURE THIS IS PRESENT AND CORRECT
        ];
    }
}