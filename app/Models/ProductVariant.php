<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock_quantity',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_product_variant');
    }

    // public function attributeValues(): MorphToMany
    // {
    //     return $this->morphToMany(AttributeValue::class, 'attributable');
    // }

    public function getVariantNameAttribute(): string
    {

        $this->loadMissing('attributeValues.attribute');
        
        return $this->attributeValues
            ->sortBy('attribute.name')
            ->map(fn($value) => $value->value)
            ->implode(' / ');
    }
}