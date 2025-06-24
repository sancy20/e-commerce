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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            // This table doesn't need its own 'id' column.
            // The primary key will be a combination of the two foreign keys.
            
            // Foreign key for the Product
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Foreign key for the AttributeValue
            $table->foreignId('attribute_value_id')->constrained()->onDelete('cascade');

            // Define a composite primary key to prevent duplicate entries
            // (e.g., the same product having the same attribute value twice).
            $table->primary(['product_id', 'attribute_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};