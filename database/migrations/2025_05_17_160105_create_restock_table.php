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
        // tabel riwayat pembelian produk
        Schema::create('restock', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_harga_beli', 15, 2); // Total harga untuk seluruh restock ini
            $table->date('tanggal_restock');
            $table->string('supplier')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // User yang melakukan restock
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock');
    }
};
