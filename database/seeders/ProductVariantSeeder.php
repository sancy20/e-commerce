<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up old attribute values that might be orphaned
        $this->command->info('Cleaning up old attribute values...');
        
        // Remove attribute values that are not attached to any variants
        \DB::statement('DELETE av FROM attribute_values av 
                       LEFT JOIN attribute_product_variant apv ON av.id = apv.attribute_value_id 
                       WHERE apv.attribute_value_id IS NULL');
        
        // Find or create attributes
        $colorAttribute = Attribute::firstOrCreate(['name' => 'Color'], ['slug' => 'color']);
        $storageAttribute = Attribute::firstOrCreate(['name' => 'Storage'], ['slug' => 'storage']);

        // Create attribute values
        $black = AttributeValue::firstOrCreate(
            ['attribute_id' => $colorAttribute->id, 'value' => 'Black'],
            ['slug' => 'black']
        );
        $white = AttributeValue::firstOrCreate(
            ['attribute_id' => $colorAttribute->id, 'value' => 'White'],
            ['slug' => 'white']
        );
        $blue = AttributeValue::firstOrCreate(
            ['attribute_id' => $colorAttribute->id, 'value' => 'Blue'],
            ['slug' => 'blue']
        );

        $storage128 = AttributeValue::firstOrCreate(
            ['attribute_id' => $storageAttribute->id, 'value' => '128GB'],
            ['slug' => '128gb']
        );
        $storage256 = AttributeValue::firstOrCreate(
            ['attribute_id' => $storageAttribute->id, 'value' => '256GB'],
            ['slug' => '256gb']
        );
        $storage512 = AttributeValue::firstOrCreate(
            ['attribute_id' => $storageAttribute->id, 'value' => '512GB'],
            ['slug' => '512gb']
        );

        // Find iPhone product (or create a sample one)
        $iPhone = Product::where('name', 'like', '%iPhone%')->first();
        if (!$iPhone) {
            // Create a sample iPhone product if it doesn't exist
            $iPhone = Product::create([
                'category_id' => 1, // Adjust based on your categories
                'name' => 'iPhone 16 Pro',
                'slug' => 'iphone-16-pro',
                'description' => 'The latest smartphone with a stunning display and pro-grade camera system.',
                'price' => 999.99,
                'sku' => 'SKU-Q4UB3P9C',
                'stock_quantity' => 0, // Base stock will be 0 since we're using variants
            ]);
        }

        // Clear existing variants for this product
        $iPhone->variants()->delete();
        
        $this->command->info('Cleared existing variants for iPhone 16 Pro');

        // Create product variants
        $variants = [
            ['color' => $black, 'storage' => $storage128, 'price' => 999.99, 'stock' => 25, 'sku' => 'IPHONE-BLACK-128'],
            ['color' => $black, 'storage' => $storage256, 'price' => 1099.99, 'stock' => 50, 'sku' => 'IPHONE-BLACK-256'],
            ['color' => $black, 'storage' => $storage512, 'price' => 1199.99, 'stock' => 30, 'sku' => 'IPHONE-BLACK-512'],
            ['color' => $white, 'storage' => $storage128, 'price' => 999.99, 'stock' => 20, 'sku' => 'IPHONE-WHITE-128'],
            ['color' => $white, 'storage' => $storage256, 'price' => 1099.99, 'stock' => 45, 'sku' => 'IPHONE-WHITE-256'],
            ['color' => $white, 'storage' => $storage512, 'price' => 1199.99, 'stock' => 15, 'sku' => 'IPHONE-WHITE-512'],
            ['color' => $blue, 'storage' => $storage128, 'price' => 999.99, 'stock' => 15, 'sku' => 'IPHONE-BLUE-128'],
            ['color' => $blue, 'storage' => $storage256, 'price' => 1099.99, 'stock' => 35, 'sku' => 'IPHONE-BLUE-256'],
            ['color' => $blue, 'storage' => $storage512, 'price' => 1199.99, 'stock' => 25, 'sku' => 'IPHONE-BLUE-512'],
        ];

        foreach ($variants as $variantData) {
            // Create the variant
            $variant = ProductVariant::create([
                'product_id' => $iPhone->id,
                'sku' => $variantData['sku'],
                'price' => $variantData['price'],
                'stock_quantity' => $variantData['stock'],
            ]);

            // Attach attribute values to the variant
            $variant->attributeValues()->attach([
                $variantData['color']->id,
                $variantData['storage']->id,
            ]);
        }

        $this->command->info('Product variants created successfully for iPhone 16 Pro!');
    }
}
