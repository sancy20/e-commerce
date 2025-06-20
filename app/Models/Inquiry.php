<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'recipient_id',
        'source_type',
        'subject',
        'message',
        'is_read',
        'replied_to_inquiry_id',
        'vendor_reply',
        'replied_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'source_type' => 'string',
        'replied_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'replied_to_inquiry_id');
    }

    public function parentInquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'replied_to_inquiry_id');
    }
}