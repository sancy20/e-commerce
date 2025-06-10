<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access if user is logged in AND their vendor_status is 'approved_vendor'
        if (Auth::check() && Auth::user()->isVendor()) { // isVendor() checks for 'approved_vendor'
            return $next($request);
        }

        // Redirect or abort if not an approved vendor
        return redirect()->route('dashboard.index')->with('error', 'Access Denied: You are not an approved vendor.');
    }
}