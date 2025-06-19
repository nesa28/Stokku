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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'create', 'update', 'delete'
            $table->string('description');
            $table->string('model_type'); // nama model yang dimodifikasi (e.g., 'Product')
            $table->unsignedBigInteger('model_id'); // ID dari record yang dimodifikasi
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('details')->nullable(); // menyimpan detail tambahan dalam format JSON
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
