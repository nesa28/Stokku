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
      // tabel riwayat penjualan produk
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->unsignedInteger('jumlah'); // Jumlah barang keluar
            $table->decimal('harga', 10, 2)->nullable(); // Harga jual
            $table->decimal('total_transaksi', 12, 2)->nullable()->after('harga_jual');
            $table->dateTime('tanggal_transaksi');
            $table->string('pelanggan')->comment('siapa yang beli,misal:pelanggan bakek mana gitu(opsional)')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('User yang mencatat transaksi');
            $table->timestamps();

            $table->foreign('product_id')
                  ->references('id') // Mereferensikan kolom 'product_id' di tabel 'products'
                  ->on('products')
                  ->onDelete('restrict'); // Mencegah penghapusan produk jika masih ada transaksi terkait
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
