<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get vendors and subcategories to link products to
        $vendorIds = User::where('vendor_status', 'approved_vendor')->pluck('id');
        $subcategoryIds = Category::whereNotNull('parent_id')->pluck('id');

        $products = [
            // Electronics
            ['name' => 'Pro Smartphone X1', 'price' => 999.99, 'stock' => 50, 'desc' => 'The latest smartphone with a stunning display and pro-grade camera system.'],
            ['name' => 'UltraThin Laptop', 'price' => 1299.99, 'stock' => 30, 'desc' => 'A lightweight and powerful laptop for professionals on the go.'],
            // Fashion
            ['name' => 'Classic Denim Jacket', 'price' => 89.99, 'stock' => 100, 'desc' => 'A timeless denim jacket made from 100% premium cotton.'],
            ['name' => 'Leather Ankle Boots', 'price' => 149.99, 'stock' => 75, 'desc' => 'Stylish and durable leather boots, perfect for any occasion.'],
        ];

        foreach ($products as $productData) {
            Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'description' => $productData['desc'],
                'price' => $productData['price'],
                'stock_quantity' => $productData['stock'],
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'is_featured' => (bool)random_int(0, 1),
                'vendor_id' => $vendorIds->random(),
                'category_id' => $subcategoryIds->random(),
            ]);
        }
    }
}