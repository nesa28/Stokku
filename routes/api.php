<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\API\AuthController;

//AUTHENTICATION ROUTES
// Create account
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    // Get user profile
    Route::get('/profile', [AuthController::class, 'profile']);
});
