<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Order; // Import Order model

class UserDashboardController extends Controller
{
    /**
     * Display the main user dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard.index', compact('user', 'recentOrders'));
    }

    /**
     * Display a listing of all orders for the authenticated user.
     */
    public function orders()
    {
        $user = Auth::user();
        $orders = $user->orders()->orderBy('created_at', 'desc')->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    /**
     * Display a single order for the authenticated user.
     */
    public function showOrder(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load('orderItems.product', 'shippingMethod'); // Eager load details

        return view('dashboard.show_order', compact('order'));
    }


    /**
     * Display the user's profile editing form.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            // You can add more fields here, like address, phone etc.
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->address = $request->address; // Assuming you added this to users table
        $user->phone = $request->phone;     // Assuming you added this to users table
        $user->save();

        return redirect()->route('dashboard.profile')->with('success', 'Profile updated successfully!');
    }
}