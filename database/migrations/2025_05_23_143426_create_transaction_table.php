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
            $table->decimal('total_transaksi', 12, 2)->nullable(); // total keseluruhan transaksi
            $table->dateTime('tanggal_transaksi');
            $table->string('pelanggan')->comment('siapa yang beli,misal:pelanggan bakek mana gitu(opsional)')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('User yang mencatat transaksi');
            $table->timestamps();

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
