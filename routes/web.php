<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Diperlukan untuk Auth::routes()

// Impor semua Controller yang akan digunakan
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RestockController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return view('welcome');
});

// Rute Otentikasi Laravel
// Baris ini akan otomatis mendaftarkan semua rute yang diperlukan untuk: login, logout, register,verifikasi email, dan lupa kata sandi
// sehingga tidak perlu membuat rute atau controller terpisah untuk fitur-fitur ini.
Auth::routes();

// Rute Halaman Utama Setelah Login (Dashboard)
Route::get('/home', [HomeController::class, 'index'])->name('home');


### Rute yang Membutuhkan Otentikasi (Harus Login Dulu, Jika belum login akan diarahkan ke halaman login.)

Route::middleware(['auth'])->group(function () {

    // dengan memakai Route::resource('nama_resource', ControllerClass::class)
    // Laravel secara otomatis membuatkan 7 rute standar CRUD (Create, update, edit, destroy, index, store,show)
    Route::resource('products', ProductsController::class);

    Route::resource('transactions', TransactionController::class);

    Route::resource('restocks', RestockController::class);

    // Mengelola data user aplikasi (biasanya untuk peran admin).
    Route::resource('users', UserController::class);

});


Route::get('/tentang-kami', function () {
    return view('about');
 });
