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
        //untuk mengelola sesi pengguna, melacak siapa yang sedang login, dan menyimpan data sesi sementara
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();//Menyimpan alamat IP dari perangkat yang digunakan
            $table->text('user_agent')->nullable(); // menyimpan string User-Agent dari klien (biasanya browser web) yang memulai atau menggunakan sesi tersebut.
            $table->longText('payload');//Tempat penyimpanan utama untuk semua data sesi aktif pengguna
            $table->integer('last_activity')->index(); //menyimpan timestamp (waktu) dari aktivitas terakhir pengguna pada sesi tersebut
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
