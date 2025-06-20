<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function ($attributeValue) {
            $attributeValue->slug = Str::slug($attributeValue->value);
        });

        static::updating(function ($attributeValue) {
            $attributeValue->slug = Str::slug($attributeValue->value);
        });
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'attribute_product_variant');
    }
}