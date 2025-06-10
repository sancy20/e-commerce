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
            // To track upgrade requests
            $table->string('upgrade_request_status')->nullable()->after('vendor_tier');
            // Possible values: null (no request), 'pending_upgrade', 'approved_upgrade', 'rejected_upgrade'
            $table->string('requested_vendor_tier')->nullable()->after('upgrade_request_status');
            // The tier they are requesting (e.g., 'Gold', 'Diamond')
            $table->timestamp('upgrade_requested_at')->nullable()->after('requested_vendor_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['upgrade_request_status', 'requested_vendor_tier', 'upgrade_requested_at']);
        });
    }
};