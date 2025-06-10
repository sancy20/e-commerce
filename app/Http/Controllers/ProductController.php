<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products for the frontend with search and filtering.
     */
    public function index(Request $request)
    {
        // Start with the base query without an initial default sort
        $query = Product::with('category');

        // --- Search Functionality ---
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        // --- Category Filtering ---
        if ($request->has('category') && $request->input('category') != '') {
            $categoryId = $request->input('category');
            $query->where('category_id', $categoryId);
        }

        // --- Price Filtering ---
        if ($request->has('min_price') && $request->input('min_price') != '') {
            $minPrice = (float) $request->input('min_price');
            $query->where('price', '>=', $minPrice);
        }
        if ($request->has('max_price') && $request->input('max_price') != '') {
            $maxPrice = (float) $request->input('max_price');
            $query->where('price', '<=', $maxPrice);
        }

        // --- Sorting ---
        // Apply sorting based on request, with a default if no sort is specified
        $sortBy = $request->input('sort', 'latest'); // Default to 'latest' if not specified

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
            default: // Fallback to 'latest' if sort parameter is invalid or empty
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString(); // Paginate and append query string to links
        $categories = Category::orderBy('name')->get(); // Get all categories for filter dropdown

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('variants.attributeValues.attribute'); // Eager load product variants and their attributes

        // Fetch all attributes and their values for variant selection dropdowns
        $attributes = Attribute::with('values')->get();

        return view('products.show', compact('product', 'attributes'));
    }
}