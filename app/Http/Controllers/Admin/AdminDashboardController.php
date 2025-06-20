<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function index()
    {
        Log::info('AdminDashboardController@index initiated.');
        
        $pendingVendorApplicationsCount = User::where('vendor_status', 'pending_vendor')->count();
        $pendingReviewApprovalsCount = Review::where('is_approved', false)->count();
        $pendingUpgradeRequestsCount = User::where('upgrade_request_status', 'pending_upgrade')->count();

        $newOrdersCount = Order::where('order_status', 'pending')
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