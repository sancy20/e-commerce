<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function ($attribute) {
            $attribute->slug = Str::slug($attribute->name);
        });

        static::updating(function ($attribute) {
            $attribute->slug = Str::slug($attribute->name);
        });
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}