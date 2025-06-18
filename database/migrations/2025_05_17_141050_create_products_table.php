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
        // membuat tabel products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->string('satuan')->comment('Satuan(misal: dus, krg)');
            $table->unsignedInteger('stok')->default(0);
            $table->decimal('harga_satuan', 10, 2)->unsigned()->default(0.00);// untuk harga dalam satuan //nama kolom, maks digit keseluruhan 10, 2 angka di belakang koma,unsigned(): harga tidak mungkin bernilai negatif, dan default(0.00): Jika produk baru secara default memiliki harga 0.
            $table->boolean('bisa_atau_tdk_diecer')->default(false)->comment('Apakah produk bisa dijual eceran?');
            $table->string('unit_eceran')->nullable()->comment('Eceran (misal: kg, bks)');
            $table->decimal('harga_eceran_per_unit', 10, 2)->unsigned()->default(0.00)->nullable(); // harga untuk barang dalam bentuk eceran
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
