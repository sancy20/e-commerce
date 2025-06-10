<x-mail::message>
# New Order Notification

@if ($recipientType === 'admin')
A new order has been placed on your platform!
@else
A new order containing your products has been placed!
@endif

**Order #{{ $order->order_number }}** has been received.

---

**Order Details:**

@if ($recipientType === 'admin')
| Product       | Quantity | Price       | Subtotal    | Vendor        |
| :------------ | :------- | :---------- | :---------- | :------------ |
@foreach ($order->orderItems as $item)
| {{ $item->product->name ?? 'Product N/A' }} | {{ $item->quantity }}     | ${{ number_format($item->price, 2) }} | ${{ number_format($item->quantity * $item->price, 2) }} | {{ $item->product->vendor->name ?? 'N/A' }} |
@endforeach
@else {{-- For vendor notification --}}
| Product       | Quantity | Price       | Subtotal    |
| :------------ | :------- | :---------- | :---------- |
@foreach ($vendorItems as $item)
| {{ $item->product->name ?? 'Product N/A' }} | {{ $item->quantity }}     | ${{ number_format($item->price, 2) }} | ${{ number_format($item->quantity * $item->price, 2) }} |
@endforeach
@endif
| **Subtotal** |          |             | **${{ number_format($order->total_amount - $order->shipping_cost, 2) }}** |
| **Shipping** |          |             | **${{ number_format($order->shipping_cost, 2) }}** |
| **Total** |          |             | **${{ number_format($order->total_amount, 2) }}** |

**Customer Info:**
Name: {{ $order->user->name ?? 'Guest User' }}
Email: {{ $order->user->email ?? 'N/A' }}
Shipping Address: {{ $order->shipping_address }}

---

You can view the full order details in your admin panel.

<x-mail::button :url="route('admin.orders.show', $order)">
View Order in Admin Panel
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>