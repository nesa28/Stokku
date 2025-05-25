<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController; // Tambahkan ini di bagian atas file

// ... routes lain Anda ...

// Ini akan mendaftarkan 7 route CRUD standar untuk ProductController
Route::resource('products', ProductController::class);
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/products', [ProductController::class, 'index'])->name('products.index');
});

// Route autentikasi bawaan Laravel
Auth::routes(['verify' => true]);

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

