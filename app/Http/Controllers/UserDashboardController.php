<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Order;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->orderBy('created_at', 'desc')->take(5)->get();

        return view('dashboard.index', compact('user', 'recentOrders'));
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = $user->orders()->orderBy('created_at', 'desc')->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load('orderItems.product', 'shippingMethod');

        return view('dashboard.show_order', compact('order'));
    }


    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }

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
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('dashboard.profile')->with('success', 'Profile updated successfully!');
    }
}