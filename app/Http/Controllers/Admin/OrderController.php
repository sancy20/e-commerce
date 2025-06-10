<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index()
    {
        // Eager load user and order items for efficient data retrieval
        $orders = Order::with('user', 'orderItems.product')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10); // Paginate for large datasets

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Eager load relationships for the single order view
        $order->load('user', 'orderItems.product');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified order in storage.
     */
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

    /**
     * Remove the specified order from storage.
     * Use with extreme caution.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')
                         ->with('success', 'Order deleted successfully.');
    }

    // Since we used ->except(['create', 'store']), these methods are not needed:
    // public function create() { /* ... */ }
    // public function store(Request $request) { /* ... */ }
}