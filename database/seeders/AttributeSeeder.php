<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color' => ['Black', 'White', 'Silver', 'Gold', 'Blue', 'Red'],
            'Size' => ['S', 'M', 'L', 'XL', 'XXL'],
            'Storage' => ['64GB', '128GB', '256GB', '512GB', '1TB'],
            'Material' => ['Cotton', 'Polyester', 'Leather', 'Aluminum', 'Plastic'],
        ];

        foreach ($attributes as $attributeName => $values) {
            $attribute = Attribute::create(['name' => $attributeName]);

            foreach ($values as $value) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }
        }
    }
}