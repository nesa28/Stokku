<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Products;    // Import Products model
use App\Models\Transaction; // Import Transaction model
use App\Models\Restock;     // Import Restock model
use App\Models\User;        // Import User model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth; // Import Auth facade

class DashboardController extends Controller
{
    public function index(): View
    {
        // Dapatkan ID pengguna yang sedang login
        $userId = Auth::id();

        // Ambil Aktivitas Terbaru untuk pengguna ini
        $recentActivities = Activity::where('user_id', $userId) // <-- Filter berdasarkan user_id
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // --- Statistik Ringkasan (Filter berdasarkan user_id) ---

        // Total Produk Unik (yang dimiliki oleh pengguna saat ini)
        // Ini akan menghitung jumlah BARIS produk yang dimiliki user, bukan total kuantitas stoknya
        $totalProducts = Products::where('user_id', $userId)->count(); // <-- Diubah dari sum('stok') ke count()

        // Total Transaksi (yang dibuat oleh pengguna saat ini)
        $totalTransactions = Transaction::where('user_id', $userId)->count(); // <-- Sudah benar

        // Total Restock (yang dibuat oleh pengguna saat ini)
        $totalRestocks = Restock::where('user_id', $userId)->count(); // <-- Sudah benar

        // Total Pengguna (Ini tetap hitungan global, menunjukkan jumlah total pengguna di sistem)
        $totalUsers = User::count();

        // --- Produk Stok Rendah (Filter berdasarkan user_id) ---
        // Tentukan ambang batas untuk 'stok rendah'
        $lowStockThreshold = 5;

        $lowStockProducts = Products::where('user_id', $userId) // <-- Filter berdasarkan user_id
            ->where('stok', '<=', $lowStockThreshold)
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();

        // --- Kirim semua data ke view ---
        return view('dashboard', compact(
            'recentActivities',
            'totalProducts',
            'totalTransactions',
            'totalRestocks',
            'totalUsers',
            'lowStockProducts',
            'lowStockThreshold'
        ));
    }
}
