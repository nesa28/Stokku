<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Controller untuk halaman utama dan about
class HomeController extends Controller
{
    // Middleware auth untuk membatasi akses hanya user yang sudah login
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Tampilkan halaman home
    public function index()
    {
        return view('home');
    }

    // Tampilkan halaman about
    public function about()
    {
        return view('about');
    }
}
