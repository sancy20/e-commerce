<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;
    public string $recipientType;

    public function __construct(Order $order, string $recipientType = 'admin')
    {
        $this->order = $order;
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

        if ($this->recipientType === 'admin') {
            $message = 'New order (#' . $this->order->order_number . ') placed on your platform.';
            $url = route('admin.orders.show', $this->order->id);
        } elseif ($this->recipientType === 'vendor') {
            $message = 'New order (#' . $this->order->order_number . ') containing your products received.';
            $url = route('vendor.orders.show', $this->order->id);
        }

        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => (float)$this->order->total_amount,
            'customer_name' => $this->order->user->name ?? 'Guest',
            'message' => $message,
            'url' => $url,
            'icon' => 'fa-shopping-cart',
            'source_type' => 'order', // <--- ENSURE THIS IS PRESENT AND CORRECT
        ];
    }
}