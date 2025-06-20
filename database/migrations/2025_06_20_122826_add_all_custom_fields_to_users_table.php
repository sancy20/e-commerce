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
            // All columns are added in the correct order in ONE place
            $table->string('vendor_status')->default('customer')->after('email');
            $table->boolean('is_admin')->default(false)->after('vendor_status');
            $table->string('vendor_tier')->nullable()->after('is_admin');
            $table->decimal('commission_rate', 8, 4)->default(0)->after('vendor_tier');
            $table->string('phone')->nullable()->after('commission_rate');
            $table->string('address')->nullable()->after('phone');
            $table->string('business_name')->nullable()->after('address');
            $table->string('business_address')->nullable()->after('business_name');
            $table->text('business_description')->nullable()->after('business_address');
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