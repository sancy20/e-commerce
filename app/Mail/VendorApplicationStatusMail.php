<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class VendorApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $status;
    public ?string $message;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $status, ?string $message = null)
    {
        $this->user = $user;
        $this->status = $status;
        $this->message = $message;
    }

    public function envelope(): Envelope
    {
        $subject = 'Your Vendor Application Status Update';
        if ($this->status === 'approved') {
            $subject = 'Congratulations! Your Vendor Application Has Been Approved!';
        } elseif ($this->status === 'rejected') {
            $subject = 'Update on Your Vendor Application';
        }

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vendor.application_status',
            with: [
                'user' => $this->user,
                'status' => $this->status,
                'message' => $this->message,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}