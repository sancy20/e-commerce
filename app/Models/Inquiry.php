<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // For replies

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
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'source_type' => 'string',
    ];

    /**
     * Get the customer who sent the inquiry.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the product related to the inquiry (if any).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user (vendor/admin) who is the recipient of the inquiry.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get replies to this inquiry (if implementing threading).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'replied_to_inquiry_id');
    }

    /**
     * Get the parent inquiry if this is a reply.
     */
    public function parentInquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'replied_to_inquiry_id');
    }
}