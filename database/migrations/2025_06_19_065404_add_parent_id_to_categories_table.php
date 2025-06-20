<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // This column will link a sub-category to its parent category's ID.
            // It's nullable because main categories have no parent.
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the column
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};