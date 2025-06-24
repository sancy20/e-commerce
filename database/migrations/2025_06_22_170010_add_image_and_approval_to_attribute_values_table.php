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
        Schema::table('attribute_values', function (Blueprint $table) {
            // This column will store the path to the color swatch or image.
            $table->string('image')->nullable()->after('value');

            // This boolean tracks if an admin has approved the value.
            // We default it to 'true' so all your existing values are automatically approved.
            $table->boolean('is_approved')->default(true)->after('image');

            // This tracks which vendor requested a new value, if any.
            $table->foreignId('requested_by_vendor_id')->nullable()->after('is_approved')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            // It's important to drop the foreign key constraint before the column.
            $table->dropForeign(['requested_by_vendor_id']);
            $table->dropColumn(['image', 'is_approved', 'requested_by_vendor_id']);
        });
    }
};
