<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Review;
use App\Models\VendorTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'is_featured':
                    $query->where('is_featured', true);
                    break;
                case 'top_rated':
                    $query->whereHas('approvedReviews')->withAvg('approvedReviews as average_rating', 'rating')->orderByDesc('average_rating');
                    break;
                case 'new_arrivals':
                    $query->where('created_at', '>=', now()->subMonth())->latest();
                    break;
                case 'top_deals':
                    $query->orderBy('price', 'desc');
                    break;
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float)$request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float)$request->input('max_price'));
        }

        if ($request->filled('tiers') && is_array($request->tiers)) {
            $tierNames = array_keys($request->tiers);
            $query->whereHas('vendor', function ($q) use ($tierNames) {
                $q->whereIn('vendor_tier', $tierNames);
            });
        }

        if ($request->filled('rating')) {
            $minRating = (float)$request->input('rating');
            if ($minRating > 0) {
                $query->whereHas('approvedReviews', function ($subQuery) use ($minRating) {
                    $subQuery->select(DB::raw('AVG(rating) as avg_rating'))
                             ->groupBy('product_id')
                             ->having('avg_rating', '>=', $minRating);
                }, '>=', 1);
            }
        }
        
        $query->with(['category', 'vendor', 'approvedReviews'])
              ->withCount(['orderItems as sold_count' => function ($subQuery) {
                  $subQuery->select(DB::raw('COALESCE(SUM(order_items.quantity), 0)'))
                         ->join('orders', 'order_items.order_id', '=', 'orders.id')
                         ->whereIn('orders.order_status', ['pending', 'processing', 'shipped', 'completed']);
              }]);
        
        $sortBy = $request->input('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc': $query->orderBy('price', 'asc'); break;
            case 'price_desc': $query->orderBy('price', 'desc'); break;
            default: $query->orderBy('created_at', 'desc'); break;
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::all();
        $vendorRatings = collect(); 
    
        return view('products.index', compact('products', 'categories', 'vendorRatings'));
    }

    public function show(Product $product)
    {
        $product->load([
            'variants.attributeValues.attribute',
            'approvedReviews.user'
        ]);
        $attributes = Attribute::with('values')->get();
        return view('products.show', compact('product', 'attributes'));
    }
}