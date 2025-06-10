<x-mail::message>
# Order Confirmation

Thank you for your order, **{{ $order->user->name ?? 'Customer' }}**!

Your order #**{{ $order->order_number }}** has been successfully placed.

---

**Order Details:**

| Product       | Quantity | Price       | Subtotal    |
| :------------ | :------- | :---------- | :---------- |
@foreach ($order->orderItems as $item)
| {{ $item->product->name ?? 'Product N/A' }} | {{ $item->quantity }}     | ${{ number_format($item->price, 2) }} | ${{ number_format($item->quantity * $item->price, 2) }} |
@endforeach
| **Subtotal** |          |             | **${{ number_format($order->total_amount - $order->shipping_cost, 2) }}** |
| **Shipping** |          |             | **${{ number_format($order->shipping_cost, 2) }}** |
| **Total** |          |             | **${{ number_format($order->total_amount, 2) }}** |

**Shipping Address:**
{{ $order->shipping_address }}

**Payment Method:**
{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}

---

You can view your order details in your dashboard.

<x-mail::button :url="route('dashboard.orders.show', $order)">
View Your Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>