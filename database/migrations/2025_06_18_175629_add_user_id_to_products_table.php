<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add the user_id column
            // nullable() because existing products might not have a user_id if created before this change
            // constrained('users') creates a foreign key to the 'users' table
            // onDelete('set null') means if a user is deleted, their products' user_id will become null
            // after('id') places the column right after the 'id' column for good order
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
            // Then drop the column
            $table->dropColumn('user_id');
        });
    }
};
