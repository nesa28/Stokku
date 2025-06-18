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
            // Add a new column to store how many eceran units are in one satuan unit
            // It should be nullable if not all products can be eceran
            // Or default to 1 if it implies "1 satuan unit = 1 eceran unit" by default
            $table->integer('jumlah_eceran_per_satuan')
                  ->nullable() // Can be null if 'bisa_atau_tdk_diecer' is false
                  ->after('unit_eceran') // Place it after the unit_eceran string field
                  ->comment('Jumlah unit eceran per satu unit satuan utama (contoh: 12 jika 1 dus = 12 pcs)');

            // Consider making 'unit_eceran' (the string name) nullable if 'bisa_atau_tdk_diecer' is false
            // If it's already nullable, you're fine.
            // $table->string('unit_eceran')->nullable()->change(); // Uncomment if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('jumlah_eceran_per_satuan');
        });
    }
};
