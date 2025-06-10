<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order; // Used for order stats
use App\Models\Product; // Used for product stats
use Carbon\Carbon; // For date manipulation

class VendorDashboardController extends Controller
{
    /**
     * Display the main vendor dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $vendorId = Auth::id();

        // Default date range for stats (can be filtered by request later)
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            // Log the error but continue with default dates
            \Log::error('Vendor Dashboard date parse error: ' . $e->getMessage());
            $startDate = Carbon::now()->subDays(30)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Get total products for this vendor
        $totalProducts = Product::where('vendor_id', $vendorId)->count();

        // Total revenue from this vendor's products in paid orders
        $totalVendorRevenue = \DB::table('order_items')
                                ->join('products', 'order_items.product_id', '=', 'products.id')
                                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                ->where('products.vendor_id', $vendorId)
                                ->whereBetween('orders.created_at', [$startDate, $endDate])
                                ->where('orders.payment_status', 'paid')
                                ->sum(\DB::raw('order_items.quantity * order_items.price'));

        // Count of orders that contain this vendor's products (regardless of full order status)
        $ordersContainingVendorProducts = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                                            $query->where('vendor_id', $vendorId);
                                        })
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->count();

        // Recent orders containing this vendor's products
        $recentVendorOrders = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                                        $query->where('vendor_id', $vendorId);
                                    })
                                    ->with(['user', 'orderItems.product'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();

          return view('vendor.dashboard', compact(
            'user', // <--- PASS THE USER OBJECT
            'totalProducts',
            'totalVendorRevenue',
            'ordersContainingVendorProducts',
            'recentVendorOrders'
        ));
    }
}