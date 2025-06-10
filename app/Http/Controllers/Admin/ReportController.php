<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon; // For date manipulation

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard.
     */
    public function index(Request $request)
    {
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


        // --- Summary Statistics ---
        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
                             ->where('payment_status', 'paid') // Only count paid orders
                             ->sum('total_amount');

        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->count();

        $paidOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])
                                ->where('payment_status', 'paid')
                                ->count();

        return view('admin.reports.index', compact(
            'totalRevenue', 'totalOrders', 'paidOrdersCount', 'startDate', 'endDate'
        ));
    }

    /**
     * Report: Sales by Date
     */
    public function salesByDate(Request $request)
    {
        // Default date range: Last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date range: ' . $e->getMessage());
        }

        $salesData = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as total_sales, COUNT(id) as total_orders')
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->where('payment_status', 'paid')
                          ->groupBy('date')
                          ->orderBy('date', 'asc')
                          ->get();

        return view('admin.reports.sales_by_date', compact('salesData', 'startDate', 'endDate'));
    }

    /**
     * Report: Best Selling Products
     */
    public function productSales(Request $request)
    {
        // Default date range: Last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date range: ' . $e->getMessage());
        }

        $productSales = \DB::table('order_items')
                           ->selectRaw('products.name as product_name, products.sku as product_sku, SUM(order_items.quantity) as total_quantity_sold, SUM(order_items.quantity * order_items.price) as total_revenue')
                           ->join('products', 'order_items.product_id', '=', 'products.id')
                           ->join('orders', 'order_items.order_id', '=', 'orders.id')
                           ->whereBetween('orders.created_at', [$startDate, $endDate])
                           ->where('orders.payment_status', 'paid')
                           ->groupBy('products.id', 'products.name', 'products.sku')
                           ->orderBy('total_revenue', 'desc')
                           ->paginate(15)
                           ->withQueryString();

        return view('admin.reports.product_sales', compact('productSales', 'startDate', 'endDate'));
    }

    /**
     * Report: Best Selling Categories
     */
    public function categorySales(Request $request)
    {
        // Default date range: Last 30 days
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        try {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            if ($startDate->greaterThan($endDate)) {
                throw new \Exception("Start date cannot be after end date.");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date range: ' . $e->getMessage());
        }

        $categorySales = \DB::table('order_items')
                            ->selectRaw('categories.name as category_name, SUM(order_items.quantity) as total_quantity_sold, SUM(order_items.quantity * order_items.price) as total_revenue')
                            ->join('products', 'order_items.product_id', '=', 'products.id')
                            ->join('categories', 'products.category_id', '=', 'categories.id')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereBetween('orders.created_at', [$startDate, $endDate])
                            ->where('orders.payment_status', 'paid')
                            ->groupBy('categories.id', 'categories.name')
                            ->orderBy('total_revenue', 'desc')
                            ->paginate(15)
                            ->withQueryString();

        return view('admin.reports.category_sales', compact('categorySales', 'startDate', 'endDate'));
    }
}