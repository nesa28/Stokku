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
       // membuat tabel baru dengan nama users
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // udh otomatis menjadi primary key
            $table->string('nama');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); //menyimpan waktu ketika alamat email pengguna diverifikasi, nullable untuk memungkinkan null
            $table->string('password');
            $table->string('nomor_telepon', 20)->nullable();
            $table->rememberToken();
            $table->timestamps(); //Membuat dua kolom timestamp otomatis: created_at (menyimpan waktu pembuatan record) dan updated_at (menyimpan waktu terakhir kali record diupdate)
        });

        //Tabel ini digunakan untuk menyimpan token reset kata sandi yang dikirimkan kepada pengguna
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
