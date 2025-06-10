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
        Schema::table('inquiries', function (Blueprint $table) {
            // Add a source_type column to distinguish inquiry origin
            $table->string('source_type')->default('general')->after('recipient_id');
            // Possible values: 'general' (from website form), 'email' (from external email)
            // You could also add 'external_email' column for sender's email if source_type is 'email'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });
    }
};