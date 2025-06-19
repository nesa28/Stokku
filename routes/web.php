<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Diperlukan untuk Auth::routes()

// Impor semua Controller yang akan digunakan
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RestockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('home');
})->name('homepage');

Route::get('/tentang-kami', function () {
    return view('about');
 });

// Rute Otentikasi Laravel
// Baris ini akan otomatis mendaftarkan semua rute yang diperlukan untuk: login, logout, register,verifikasi email, dan lupa kata sandi
// sehingga tidak perlu membuat rute atau controller terpisah untuk fitur-fitur ini.
Auth::routes(['verify' => true]);

### Rute yang Membutuhkan Otentikasi (Harus Login Dulu, Jika belum login akan diarahkan ke halaman login.)

Route::middleware(['auth'])->group(function () {

    // dengan memakai Route::resource('nama_resource', ControllerClass::class)
    // Laravel secara otomatis membuatkan 7 rute standar CRUD (Create, update, edit, destroy, index, store,show)
    Route::get('products/search', [ProductsController::class, 'search'])->name('products.search');
    Route::resource('products', ProductsController::class);

    Route::get('transactions/search', [TransactionController::class, 'search'])->name('transactions.search');
    Route::resource('transactions', TransactionController::class);

    Route::get('restocks/search', [RestockController::class, 'search'])->name('restocks.search');
    Route::resource('restocks', RestockController::class);

    // Mengelola data user aplikasi (biasanya untuk peran admin).
    Route::resource('users', UserController::class);

    // Rute untuk halaman dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});
