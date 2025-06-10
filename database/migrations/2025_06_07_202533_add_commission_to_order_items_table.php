<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Commission amount for this specific item (platform's cut)
            $table->decimal('commission_amount', 10, 2)->default(0.00)->after('price');
            // Vendor's actual earning from this item
            $table->decimal('vendor_payout_amount', 10, 2)->default(0.00)->after('commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'vendor_payout_amount']);
        });
    }
};