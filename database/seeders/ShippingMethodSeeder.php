<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingMethods = [
            [
                'name' => 'Standard Shipping',
                'description' => 'Delivery within 5-7 business days',
                'cost' => 2.00,
                'is_active' => true,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Delivery within 2-3 business days',
                'cost' => 8.99,
                'is_active' => true,
            ],
            [
                'name' => 'Overnight Shipping',
                'description' => 'Next business day delivery',
                'cost' => 19.99,
                'is_active' => true,
            ],
            [
                'name' => 'Free Shipping',
                'description' => 'Free delivery within 7-10 business days (orders over $100)',
                'cost' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::firstOrCreate(
                ['name' => $method['name']],
                $method
            );
        }

        $this->command->info('Shipping methods created successfully!');
    }
}
