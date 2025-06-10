<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // For slug generation

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The "booted" method of the model.
     * Auto-generate slug.
     */
    protected static function booted()
    {
        static::creating(function ($attribute) {
            $attribute->slug = Str::slug($attribute->name);
        });

        static::updating(function ($attribute) {
            $attribute->slug = Str::slug($attribute->name);
        });
    }

    /**
     * Get the attribute values for the attribute.
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}