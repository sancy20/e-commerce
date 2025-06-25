<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Add this line

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        // Tell Laravel to use Tailwind CSS for pagination links
        Paginator::useTailwind(); // Add this line
        // Or, if you prefer, use Bootstrap 5's Tailwind equivalent directly:
        // Paginator::useBootstrapFive(); // If you want Bootstrap 5 classes
    }
}