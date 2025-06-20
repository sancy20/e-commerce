<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product; 
use Carbon\Carbon; 

class VendorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $vendorId = Auth::id();

        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            \Log::error('Vendor Dashboard date parse error: ' . $e->getMessage());
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $totalProducts = Product::where('vendor_id', $vendorId)->count();

        $totalVendorRevenue = \DB::table('order_items')
                                ->join('products', 'order_items.product_id', '=', 'products.id')
                                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                ->where('products.vendor_id', $vendorId)
                                ->whereBetween('orders.created_at', [$startDate, $endDate])
                                ->where('orders.payment_status', 'paid')
                                ->sum(\DB::raw('order_items.quantity * order_items.price'));

        $ordersContainingVendorProducts = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                                            $query->where('vendor_id', $vendorId);
                                        })
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->count();

        $recentVendorOrders = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                                        $query->where('vendor_id', $vendorId);
                                    })
                                    ->with(['user', 'orderItems.product'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();

          return view('vendor.dashboard', compact(
            'user',
            'totalProducts',
            'totalVendorRevenue',
            'ordersContainingVendorProducts',
            'recentVendorOrders'
        ));
    }
}