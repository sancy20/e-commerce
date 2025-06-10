<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND is an admin
        if (Auth::check() && Auth::user()->isAdmin()) { // Use the isAdmin() helper method
            return $next($request);
        }

        // Redirect unauthenticated or non-admin users
        // Redirect to login if not logged in, or to dashboard with error if logged in but not admin
        if (!Auth::check()) {
            return redirect()->route('login'); // Or whatever your login route is
        }

        // User is logged in but not an admin
        return redirect()->route('dashboard.index')->with('error', 'Access Denied: You do not have administrator privileges.');
    }
}