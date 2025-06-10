<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // Import Str facade for slug generation
use Illuminate\Database\Eloquent\Collection;


class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sku',
        'image',
        'stock_quantity',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'decimal:2', // Cast price to decimal with 2 places
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name);
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the approved reviews for the product.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /**
     * Get the average rating for the product.
     */
    public function averageRating()
    {
        return $this->approvedReviews()->avg('rating');
    }

    public function vendor(): BelongsTo // Renamed user() to vendor() for clarity
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Determine if the product has any variants.
     */
    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    /**
     * Get the effective price of the product.
     * If product has variants, return min/max price of variants. Otherwise, base price.
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->hasVariants()) {
            // Return the lowest price among variants, or a range
            return (float) $this->variants->min('price') ?: (float) $this->price;
        }
        return (float) $this->price;
    }

    /**
     * Get the effective stock quantity.
     * If product has variants, sum of variant stock. Otherwise, base product stock.
     */
    public function getEffectiveStockQuantityAttribute(): int
    {
        if ($this->hasVariants()) {
            return (int) $this->variants->sum('stock_quantity');
        }
        return (int) $this->stock_quantity;
    }
}