<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'orderItems.product')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'orderItems.product');
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        $order->update($request->all());

        return redirect()->route('admin.orders.show', $order->id)
                         ->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')
                         ->with('success', 'Order deleted successfully.');
    }
}