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
        Schema::table('reviews', function (Blueprint $table) {
            // Add column for vendor's reply
            $table->text('vendor_reply')->nullable()->after('comment');
            // Add timestamp for when the reply was made
            $table->timestamp('replied_at')->nullable()->after('vendor_reply');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['vendor_reply', 'replied_at']);
        });
    }
};