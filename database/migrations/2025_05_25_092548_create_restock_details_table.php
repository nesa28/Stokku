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
        Schema::create('restock_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_id')->constrained('restock')->onDelete('cascade'); // Foreign key ke tabel restock
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict'); // Foreign key ke tabel products
            $table->integer('jumlah'); // Jumlah produk yang direstock (dalam satuan dasar)
            $table->decimal('harga_beli_per_unit', 10, 2); // Harga beli per unit produk ini
            $table->decimal('subtotal_harga_beli', 12, 2); // Jumlah * harga_beli_per_unit
            $table->timestamps();

            // Indeks untuk performa
            $table->index(['restock_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_details');
    }
};
