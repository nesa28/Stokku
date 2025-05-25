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
            $table->foreignId('product_id');
            $table->unsignedInteger('jumlah'); // Jumlah barang masuk
            $table->decimal('harga_beli', 10, 2)->nullable(); // Harga beli
            $table->decimal('total_harga', 12, 2)->nullable()->after('harga_beli')->comment('Total harga beli saat restock');
            $table->dateTime('tanggal_restock');
            $table->string('supplier')->comment('Siapa suppliernya,misal: Liu akim,dll(opsional)')->nullable();
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
        Schema::dropIfExists('restock');
    }
};
