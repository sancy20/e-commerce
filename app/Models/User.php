<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone',
        'vendor_status',
        'is_admin',
        'vendor_tier',
        'commission_rate',
        'stripe_connect_id',
        'payouts_enabled',
        'charges_enabled',
        'business_name',
        'business_address',
        'business_description',
        'upgrade_request_status',
        'requested_vendor_tier',
        'upgrade_requested_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'vendor_status' => 'string',
        'is_admin' => 'boolean',
        'vendor_tier' => 'string',
        'commission_rate' => 'decimal:4',
        'upgrade_request_status' => 'string',
        'upgrade_requested_at' => 'datetime',
        'stripe_connect_id' => 'string',
        'payouts_enabled' => 'boolean', 
        'charges_enabled' => 'boolean',
    ];

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany // <--- ADD THIS METHOD
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function isVendor(): bool
    {
        return $this->vendor_status === 'approved_vendor';
    }

    public function isPendingVendor(): bool
    {
        return $this->vendor_status === 'pending_vendor';
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isGoldVendor(): bool
    {
        return $this->vendor_tier === 'Gold' || $this->vendor_tier === 'Diamond';
    }

    public function isDiamondVendor(): bool
    {
        return $this->vendor_tier === 'Diamond';
    }

    public function hasPendingUpgradeRequest(): bool 
    {
        return $this->upgrade_request_status === 'pending_upgrade';
    }

    public function getCommissionRate(): float
    {
        return (float) $this->commission_rate;
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(VendorPayout::class, 'vendor_id');
    }

    public function getOutstandingPayoutAmount(): float
    {
        // Sum of vendor_payout_amount from all their order_items in paid orders
        $totalEarned = \DB::table('order_items')
                           ->join('products', 'order_items.product_id', '=', 'products.id')
                           ->join('orders', 'order_items.order_id', '=', 'orders.id')
                           ->where('products.vendor_id', $this->id)
                           ->where('orders.payment_status', 'paid')
                           ->sum('order_items.vendor_payout_amount');

        // Sum of all payouts already made to this vendor
        $totalPaid = $this->payouts()->where('status', 'completed')->sum('amount');

        return (float) max(0, $totalEarned - $totalPaid); // Ensure it's not negative
    }

    public function canReceivePayouts(): bool
    {
        return $this->isVendor() && $this->stripe_connect_id !== null && $this->payouts_enabled;
    }

}