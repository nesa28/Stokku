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
        Schema::table('products', function (Blueprint $table) {
            // This column will store the sequential number for each user's products
            $table->unsignedBigInteger('user_product_code')->nullable()->after('user_id');

            // Add a unique composite index to ensure (user_id, user_product_code) is unique
            // This means User A cannot have two products with user_product_code = 1
            // And it allows User A to have product 1, and User B to also have product 1
            $table->unique(['user_id', 'user_product_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'user_product_code']); // Drop the unique constraint first
            $table->dropColumn('user_product_code');
        });
    }
};
