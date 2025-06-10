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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Customer who sent it
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null'); // Optional: product it's about
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade'); // Vendor or Admin who receives it
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->foreignId('replied_to_inquiry_id')->nullable()->constrained('inquiries')->onDelete('set null'); // For replies
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};