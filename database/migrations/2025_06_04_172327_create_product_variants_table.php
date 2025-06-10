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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Link to the base product
            $table->string('sku')->unique()->nullable(); // SKU for this specific variant
            $table->decimal('price', 10, 2)->nullable(); // Price for this variant (null means use base product price)
            $table->unsignedInteger('stock_quantity')->default(0); // Stock for this specific variant
            $table->string('image')->nullable(); // Optional image for this variant
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};