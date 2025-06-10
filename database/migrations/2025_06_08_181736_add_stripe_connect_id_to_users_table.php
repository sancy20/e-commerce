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
        Schema::table('users', function (Blueprint $table) {
            // Stripe Account ID for Connect payments
            $table->string('stripe_connect_id')->nullable()->unique()->after('commission_rate');
            // Flag to indicate if their Stripe account is ready for payouts
            $table->boolean('payouts_enabled')->default(false)->after('stripe_connect_id');
            // Flag to indicate if their Stripe account requires additional action
            $table->boolean('charges_enabled')->default(false)->after('payouts_enabled'); // Can they accept charges?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_connect_id', 'payouts_enabled', 'charges_enabled']);
        });
    }
};