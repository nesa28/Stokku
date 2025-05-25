<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;

// Create account
Route::post('/register', [ProductsController::class, 'addAccount']);

// Login
Route::post('/login', [ProductsController::class, 'login']);



Route::put('/edit/{id}', [ProductsController::class, 'edit']);


Route::delete('/delete/{id}', [ProductsController::class, 'delete']);
