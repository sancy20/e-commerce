<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str; // For slug generation

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
    ];

    /**
     * The "booted" method of the model.
     * Auto-generate slug.
     */
    protected static function booted()
    {
        static::creating(function ($attributeValue) {
            $attributeValue->slug = Str::slug($attributeValue->value);
        });

        static::updating(function ($attributeValue) {
            $attributeValue->slug = Str::slug($attributeValue->value);
        });
    }

    /**
     * Get the attribute that owns the value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * The product variants that have this attribute value.
     */
    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'attribute_product_variant');
    }
}