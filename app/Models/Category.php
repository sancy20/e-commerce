<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    protected static function booted()
    {
        static::creating(fn($category) => $category->slug = Str::slug($category->name));
        static::updating(fn($category) => $category->slug = Str::slug($category->name));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeMainCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
    return $this->belongsToMany(Attribute::class, 'attribute_category');
    }
}