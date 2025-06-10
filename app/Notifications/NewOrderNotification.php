<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User; // To determine recipient type (admin/vendor)
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage; // If you want to use MailMessage here
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue // Implement ShouldQueue for async processing
{
    use Queueable;

    public Order $order;
    public string $recipientType; // 'admin' or 'vendor'
    public $vendorItems; // Specific items for vendor notification

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $recipientType = 'admin', $vendorItems = null)
    {
        $this->order = $order;
        $this->recipientType = $recipientType;
        $this->vendorItems = $vendorItems;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Store in the database
        // return ['mail', 'database']; // If you want to send email AND store in DB
    }

    /**
     * Get the array representation of the notification.
     * This is the data stored in the 'data' column of the notifications table.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = '';
        $url = '';

        if ($this->recipientType === 'admin') {
            $message = 'A new order (#' . $this->order->order_number . ') has been placed on your platform.';
            $url = route('admin.orders.show', $this->order->id);
        } elseif ($this->recipientType === 'vendor') {
            $message = 'New order (#' . $this->order->order_number . ') containing your products received.';
            $url = route('vendor.orders.show', $this->order->id);
        }

        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total_amount,
            'customer_name' => $this->order->user->name ?? 'Guest',
            'message' => $message,
            'url' => $url,
            'icon' => 'fa-shopping-cart', // Example icon for frontend
        ];
    }

    // You can optionally add a toMail() method if you want this Notification class to also send emails
    // public function toMail(object $notifiable): MailMessage
    // {
    //     // Adapt your mail template here, or use the existing Mailables from App\Mail
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }
}