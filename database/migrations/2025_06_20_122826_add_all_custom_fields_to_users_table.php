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
            // Add all custom columns in the correct order
            $table->string('vendor_status')->default('customer')->after('email');
            $table->boolean('is_admin')->default(false)->after('vendor_status');
            $table->string('vendor_tier')->nullable()->after('is_admin');
            $table->string('vendor_tier_id')->nullable()->after('vendor_tier');
            $table->decimal('commission_rate', 8, 4)->default(0)->after('vendor_tier_id');
            $table->string('phone')->nullable()->after('commission_rate');
            $table->string('address')->nullable()->after('phone');
            $table->string('business_name')->nullable()->after('address');
            $table->string('business_address')->nullable()->after('business_name');
            $table->text('business_description')->nullable()->after('business_address');
            
            // This is the column causing the current error. It must be in this file.
            $table->string('upgrade_request_status')->nullable()->after('business_description');
            $table->string('requested_vendor_tier')->nullable()->after('upgrade_request_status');
            $table->timestamp('upgrade_requested_at')->nullable()->after('requested_vendor_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // List the columns to drop in the reverse order of creation
            $table->dropColumn([
                'vendor_status',
                'is_admin',
                'vendor_tier',
                'commission_rate',
                'phone',
                'address',
                'business_name',
                'business_address',
                'business_description',
            ]);
        });
    }
};