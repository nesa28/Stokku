// File: database/migrations/YYYY_MM_DD_HHMMSS_create_transaction_details_table.php

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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transaction')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->unsignedInteger('jumlah');
            $table->string('jenis_penjualan')->comment('Jenis penjualan: satuan atau eceran');
            $table->decimal('harga_per_unit', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index(['transaction_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
