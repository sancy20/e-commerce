<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent; // Import the event
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache; // Import Cache facade
use Illuminate\Support\Facades\Log; // For logging

class LogLastEmailSent
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $details = [
            'to' => $event->message->getTo(),
            'from' => $event->message->getFrom(),
            'subject' => $event->message->getSubject(),
            'body_length' => strlen($event->message->getBody()),
            'sent_at' => now()->toDateTimeString(),
        ];

        // Store the details in cache for a short period (e.g., 5 minutes)
        Cache::put('last_email_sent_debug', $details, 300); // 300 seconds = 5 minutes

        Log::info('Email sent event captured: ' . json_encode($details));
    }
}