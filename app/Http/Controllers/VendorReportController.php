<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order; // Import Order model
use Carbon\Carbon;     // Import Carbon for date manipulation

class VendorReportController extends Controller
{
    /**
     * Display the main vendor reports dashboard.
     */
    public function index(Request $request)
    {
        $vendorId = Auth::id(); // Get the authenticated vendor's ID

        // Default date range: Last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // Ensure dates are valid and end_date is not before start_date
        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date range: ' . $e->getMessage());
        }

        // --- Vendor Summary Statistics ---
        // Total Revenue for this vendor's paid products
        $vendorTotalRevenue = \DB::table('order_items')
                                 ->join('products', 'order_items.product_id', '=', 'products.id')
                                 ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                 ->where('products.vendor_id', $vendorId)
                                 ->whereBetween('orders.created_at', [$startDate, $endDate])
                                 ->where('orders.payment_status', 'paid')
                                 ->sum(\DB::raw('order_items.quantity * order_items.price'));

        // Total Orders containing this vendor's products
        $vendorTotalOrders = Order::whereHas('orderItems.product', function ($query) use ($vendorId) {
                                        $query->where('vendor_id', $vendorId);
                                    })
                                   ->whereBetween('created_at', [$startDate, $endDate])
                                   ->count();

        // Best Selling Products for this vendor
        $vendorTopProducts = \DB::table('order_items')
                               ->selectRaw('products.name as product_name, products.image, SUM(order_items.quantity) as total_quantity_sold, SUM(order_items.quantity * order_items.price) as total_revenue')
                               ->join('products', 'order_items.product_id', '=', 'products.id')
                               ->join('orders', 'order_items.order_id', '=', 'orders.id')
                               ->where('products.vendor_id', $vendorId)
                               ->whereBetween('orders.created_at', [$startDate, $endDate])
                               ->where('orders.payment_status', 'paid')
                               ->groupBy('products.id', 'products.name', 'products.image')
                               ->orderBy('total_revenue', 'desc')
                               ->take(5) // Show top 5
                               ->get();

        return view('vendor.reports.index', compact(
            'vendorTotalRevenue', 'vendorTotalOrders', 'vendorTopProducts', 'startDate', 'endDate'
        ));
    }
}