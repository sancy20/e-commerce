<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorOrderController extends Controller
{
    /**
     * Display a listing of orders that contain products from the authenticated vendor.
     */
    public function index()
    {
        $vendorId = Auth::id();

        // Get orders that have at least one order item linked to a product from this vendor
        $orders = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->with(['user', 'orderItems.product']) // Eager load user, order items, and associated products
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('vendor.orders.index', compact('orders'));
    }

    /**
     * Display the specified order, ensuring it contains products from the authenticated vendor.
     */
    public function show(Order $order)
    {
        $vendorId = Auth::id();

        // Ensure the order actually contains products from this vendor
        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            // If the order does not contain any of this vendor's products, deny access
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load('user', 'orderItems.product', 'shippingMethod'); // Eager load all details

        return view('vendor.orders.show', compact('order', 'vendorId')); // Pass vendorId for filtering items
    }

    public function edit(Order $order)
    {
        $vendorId = Auth::id();

        // Ensure the order contains products from this vendor
        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            abort(403, 'Unauthorized access to edit this order.');
        }

        $order->load('user', 'orderItems.product', 'shippingMethod'); // Eager load all details

        return view('vendor.orders.edit', compact('order'));
    }

    /**
     * Update the specified order in storage, ensuring it contains vendor's products.
     */
    public function update(Request $request, Order $order)
    {
        $vendorId = Auth::id();
        Log::info('VendorOrderController@update initiated for order ID: ' . $order->id . ' by vendor ID: ' . $vendorId);

        // Ensure the order contains products from this vendor
        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            Log::warning('Unauthorized update attempt for order ID: ' . $order->id . ' by vendor ID: ' . $vendorId . ' (no vendor products in order).');
            abort(403, 'Unauthorized action: This order does not contain products from your store.');
        }

        $request->validate([
            // Allow vendor to change limited statuses
            'order_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            // Vendors usually shouldn't change payment_status directly, that's admin/gateway
            // 'payment_status' => 'required|string|in:pending,paid,failed,refunded', // Uncomment if vendor can manage
            'notes' => 'nullable|string|max:500', // Allow vendor to add/update notes
        ]);
        Log::info('Validation passed for order update by vendor.');


        DB::beginTransaction();
        try {
            $order->update([
                'order_status' => $request->order_status,
                'notes' => $request->notes,
            ]);
            Log::info('Order ID: ' . $order->id . ' status updated to ' . $request->order_status . ' by vendor ID: ' . $vendorId);

            DB::commit();
            Log::info('DB commit successful for vendor order update.');
            return redirect()->route('vendor.orders.show', $order->id)
                             ->with('success', 'Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Outer transaction error in VendorOrderController@update for order ID: ' . $order->id . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'An error occurred while updating the order. Please try again.');
        }
    }

    public function destroy(Order $order)
    {
        $vendorId = Auth::id();
        // Ensure the order contains products from this vendor BEFORE deleting
        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            abort(403, 'Unauthorized action: This order does not contain products from your store.');
        }

        // WARNING: Deleting an order can cause data inconsistencies and impact reports.
        // Consider changing order_status to 'cancelled' instead of actual deletion.
        $order->delete();

        return redirect()->route('vendor.orders.index')
                        ->with('success', 'Order deleted successfully.');
    }

    // Optional: Add update method if vendors can change order status (e.g., shipped for their items)
    // public function update(Request $request, Order $order)
    // {
    //     $vendorId = Auth::id();
    //
    //     // Basic check: Ensure order contains products from this vendor
    //     $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
    //         return $item->product->vendor_id === $vendorId;
    //     });
    //     if (!$hasVendorProducts) {
    //         abort(403, 'Unauthorized action.');
    //     }
    //
    //     $request->validate([
    //         'order_status' => 'required|string|in:processing,shipped', // Limited statuses for vendor
    //         // Vendor usually shouldn't change payment_status directly unless it's COD confirmed.
    //     ]);
    //
    //     // For a real multi-vendor, updating order status is complex if one order has multiple vendors' products.
    //     // You might need a separate 'vendor_order_status' on order_items or a pivot table.
    //     // For now, this is a simplified example updating the main order status.
    //     $order->order_status = $request->order_status;
    //     $order->save();
    //
    //     return redirect()->route('vendor.orders.show', $order->id)
    //                      ->with('success', 'Order status updated successfully.');
    // }
}