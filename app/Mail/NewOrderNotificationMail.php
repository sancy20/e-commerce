<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class NewOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public $recipientType; // 'admin' or 'vendor'
    public $vendorItems; // Only for vendor notifications

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $recipientType = 'admin', $vendorItems = null)
    {
        $this->order = $order;
        $this->recipientType = $recipientType;
        $this->vendorItems = $vendorItems; // Filtered items for vendor
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'New Order Notification - #' . $this->order->order_number;
        if ($this->recipientType === 'vendor') {
            $subject = 'New Order for Your Products - #' . $this->order->order_number;
        }

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.new_order',
            with: [
                'order' => $this->order,
                'recipientType' => $this->recipientType,
                'vendorItems' => $this->vendorItems,
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