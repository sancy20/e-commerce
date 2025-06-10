<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // For vendor applications, upgrade requests
use App\Models\Review; // For pending reviews
use App\Models\Order; // For new orders (though we have a different notification for this)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging

class AdminDashboardController extends Controller
{
    /**
     * Display the main admin dashboard with pending tasks.
     */
    public function index()
    {
        Log::info('AdminDashboardController@index initiated.');

        // Get counts of pending items
        $pendingVendorApplicationsCount = User::where('vendor_status', 'pending_vendor')->count();
        $pendingReviewApprovalsCount = Review::where('is_approved', false)->count();
        $pendingUpgradeRequestsCount = User::where('upgrade_request_status', 'pending_upgrade')->count();

        // Optionally, count new paid orders not yet processed (status 'processing' but maybe needs admin review)
        // Or just new paid orders (status 'paid')
        $newOrdersCount = Order::where('order_status', 'pending') // Assuming 'pending' means awaiting admin action
                               ->where('payment_status', 'paid')
                               ->count();

        return view('admin.dashboard.index', compact(
            'pendingVendorApplicationsCount',
            'pendingReviewApprovalsCount',
            'pendingUpgradeRequestsCount',
            'newOrdersCount'
        ));
    }
}