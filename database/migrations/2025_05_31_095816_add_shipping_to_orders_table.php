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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->onDelete('set null')->after('notes');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('shipping_method_id');
            // If you change total_amount in checkout, you might want to rename it to subtotal
            // and add a final_total_amount. For now, we'll just adjust total_amount.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn(['shipping_method_id', 'shipping_cost']);
        });
    }
};