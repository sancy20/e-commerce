<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics' => ['Smartphones', 'Laptops', 'Cameras', 'Headphones'],
            'Fashion' => ['Men\'s Clothing', 'Women\'s Clothing', 'Shoes', 'Accessories'],
            'Home & Garden' => ['Furniture', 'Kitchenware', 'Lighting', 'Gardening Tools'],
            'Books' => ['Fiction', 'Non-Fiction', 'Science Fiction', 'Biographies'],
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_id' => null
            ]);

            foreach ($subCategories as $subCategoryName) {
                Category::create([
                    'name' => $subCategoryName,
                    'slug' => Str::slug($subCategoryName),
                    'parent_id' => $parent->id
                ]);
            }
        }
    }
}