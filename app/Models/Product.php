<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'category_id', 'name', 'slug', 'description', 
        'price', 'sku', 'image', 'stock_quantity', 'is_featured',
        'vendor_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];
    

    protected static function booted()
    {
        static::creating(fn($product) => $product->slug = Str::slug($product->name));
        static::updating(fn($product) => $product->slug = Str::slug($product->name));
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        if ($this->images()->exists()) {
            return asset('storage/' . $this->images->first()->path);
        }
        if ($this->variants()->whereNotNull('image')->exists()) {
            return asset('storage/' . $this->variants->firstWhere('image')->image);
        }

        return '';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function variants(): HasMany
    {
    return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues(): MorphToMany
    {
        return $this->morphToMany(AttributeValue::class, 'attributable', 'attributables');
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->hasVariants()) {
            return (float) $this->variants->min('price') ?: (float) $this->price;
        }
        return (float) $this->price;
    }

    public function getEffectiveStockQuantityAttribute(): int
    {
        if ($this->hasVariants()) {
            return (int) $this->variants->sum('stock_quantity');
        }
        return (int) $this->stock_quantity;
    }

    public function getVariantNameAttribute(): string
    {
        if ($this->attributeValues->isEmpty()) {
            return '';
        }
        $this->loadMissing('attributeValues.attribute');
        return $this->attributeValues
            ->sortBy('attribute.name')
            ->map(fn($value) => $value->value)
            ->implode(' / ');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getMinPriceAttribute(): float
    {
        if ($this->hasVariants()) {
            return (float) $this->variants->min('price') ?: (float) $this->price;
        }
        return (float) $this->price;
    }

    public function getPriceRangeAttribute(): string
    {
        if (!$this->hasVariants()) {
            return '$' . number_format($this->price, 2);
        }

        $minPrice = $this->variants->min('price');
        $maxPrice = $this->variants->max('price');

        if ($minPrice == $maxPrice) {
            return '$' . number_format($minPrice, 2);
        }

        return '$' . number_format($minPrice, 2) . ' - $' . number_format($maxPrice, 2);
    }

    public function images(): HasMany
    {
    return $this->hasMany(ProductImage::class);
    }

}