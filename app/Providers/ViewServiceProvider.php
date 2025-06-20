<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Category;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.navbar', function ($view) {
            $mainCategoriesWithSubcategories = Category::whereNull('parent_id')
                ->with('children')
                ->orderBy('name')
                ->get();
            
            $view->with('mainCategoriesWithSubcategories', $mainCategoriesWithSubcategories);
        });
    }
}