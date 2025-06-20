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
        // This table links attribute values to other models (like Products and ProductVariants)
        Schema::create('attributables', function (Blueprint $table) {
            // This is the ID from the attribute_values table (e.g., the ID for 'Red' or 'Small')
            $table->foreignId('attribute_value_id')->constrained()->onDelete('cascade');

            // This creates the two columns needed for a polymorphic relationship:
            // 1. attributable_id (e.g., the product_id)
            // 2. attributable_type (e.g., 'App\Models\Product')
            $table->morphs('attributable');

            // Set a composite primary key to prevent duplicate entries
            $table->primary(['attribute_value_id', 'attributable_id', 'attributable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributables');
    }
};