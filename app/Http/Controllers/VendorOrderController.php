<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorOrderController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();

        $orders = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                            $query->where('vendor_id', $vendorId);
                        })
                        ->with(['user', 'orderItems.product'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('vendor.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $vendorId = Auth::id();

        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load('user', 'orderItems.product', 'shippingMethod');

        return view('vendor.orders.show', compact('order', 'vendorId'));
    }

    public function edit(Order $order)
    {
        $vendorId = Auth::id();

        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            abort(403, 'Unauthorized access to edit this order.');
        }

        $order->load('user', 'orderItems.product', 'shippingMethod');

        return view('vendor.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $vendorId = Auth::id();
        Log::info('VendorOrderController@update initiated for order ID: ' . $order->id . ' by vendor ID: ' . $vendorId);

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
            'notes' => 'nullable|string|max:500',        
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
        $hasVendorProducts = $order->orderItems->contains(function ($item) use ($vendorId) {
            return $item->product->vendor_id === $vendorId;
        });

        if (!$hasVendorProducts) {
            abort(403, 'Unauthorized action: This order does not contain products from your store.');
        }

        $order->delete();

        return redirect()->route('vendor.orders.index')
                        ->with('success', 'Order deleted successfully.');
    }
}