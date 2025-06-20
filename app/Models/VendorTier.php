<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'commission_rate',
        'description',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:4',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'vendor_tier_id');
    }
}