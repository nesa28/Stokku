<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Products;    // Import Products model
use App\Models\Transaction; // Import Transaction model
use App\Models\Restock;     // Import Restock model
use App\Models\User;        // Import User model
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controller untuk menampilkan dashboard
class DashboardController extends Controller
{
    public function index(): View
    {
        // Get the ID of the currently authenticated user
        $userId = auth()->id();

        // Fetch Recent Activities for the current user
        $recentActivities = Activity::where('user_id', $userId) // <-- Filtered by user_id
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // --- NEW: Summary Statistics (Filtered by user_id) ---

        // Total Products (Sum of stock for products owned by the current user)
        $totalProducts = Products::where('user_id', $userId)->sum('stok'); // <-- Filtered by user_id

        // Total Transactions (Count of transactions created by the current user)
        $totalTransactions = Transaction::where('user_id', $userId)->count(); // <-- Filtered by user_id

        // Total Restocks (Count of restocks created by the current user)
        $totalRestocks = Restock::where('user_id', $userId)->count(); // <-- Filtered by user_id

        // Total Users (This remains a global count as it's the total users in the system)
        $totalUsers = User::count();

        // --- NEW: Low Stock Products (Filtered by user_id) ---
        // Define a threshold for 'low stock'
        $lowStockThreshold = 5; // Example: products with 5 or fewer units are considered low stock

        $lowStockProducts = Products::where('user_id', $userId) // <-- Filtered by user_id
            ->where('stok', '<=', $lowStockThreshold)
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();

        // --- Pass all data to the view ---
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
