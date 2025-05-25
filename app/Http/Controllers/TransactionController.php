<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse; // Digunakan untuk method yang mengembalikan redirect

class TransactionController extends Controller
{
   // Index - Menampilkan semua data
    public function index(): View
    {
        $restocks = Transaction::with(['product', 'user'])->latest()->paginate(10); // Memuat relasi 'product' dan 'user' untuk ditampilkan
        return view('transaction.index', compact('transaction'));
    }

    // Create - Menampilkan form tambah data
    public function create(): View
    {
        $products = Products::all(); // Ambil semua produk untuk dropdown di form
        $users = User::all();
        return view('transaction.create', compact('products', 'users'));
    }

    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'product_id' =>'required|exists:products,id', // product_id harus ada di tabel products
            'jumlah'=>'required',
            'harga'=>'required',
            'tanggal_transaksi'=>'required|date',
            'pelanggan'=>'nullable',
            'user_id'=>'nullable|exists:users,id',// user_id harus ada di tabel users
        ]);

        // Hitung total harga beli jika harga_beli ada
        $total_transaksi = null;
        if ($request->filled('harga') && $request->filled('jumlah')) {
            $total_transaksi = $request->harga * $request->jumlah;
        }

       // Buat record transaction baru di database
        $transaction = Transaction::create([
            'product_id' => $request->product_id,
            'jumlah' => $request->jumlah,
            'harga' => $request->harga, // ambil dari database
            'total_transaksi' => $total_transaksi, // Simpan hasil perhitungan
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'supplier' => $request->supplier,
            'user_id' => $request->user_id ?? Auth::id(), // Jika user_id tidak diisi, pakai user yang login
        ]);

        //Perbarui stok produk setelah transaction
        $product = Products::find($request->product_id);
        if ($product) {
            $product->stok += $request->jumlah;
            $product->save();
        }

        return redirect()->route('transaction')
            ->with('success', 'Riwayat transaction berhasil ditambahkan!');
    }

    //Menampilkan detail riwayat transaction tertentu.
     public function show(Transaction $transaction): View
    {
        return view('transaction.show', compact('transaction'));
    }

    // Edit - Menampilkan form edit
    public function edit($id): View
    {
        // Ambil semua produk dan user untuk dropdown di form edit
        $products = Products::all();
        $users = User::all();
        $transaction = Transaction::find($id);
        return view('transaction.edit', compact('transaction'));
    }

    // Update - Memperbarui data
    public function update(Request $request,Transaction $transaction): RedirectResponse
    {
        // Simpan jumlah lama sebelum update untuk perhitungan stok
        $oldJmlh = $transaction->jumlah;
        $oldProductId = $transaction->product_id;

        $request->validate([
           'product_id' =>'required|exists:products,id',
            'jumlah'=>'required',
            'harga'=>'required',
            'tanggal_transaksi'=>'required|date',
            'pelanggan'=>'nullable',
            'user_id'=>'nullable|exists:users,id',
        ]);

        // Hitung total harga beli jika harga_beli ada
        $total_transaksi = null;
        if ($request->filled('harga') && $request->filled('jumlah')) {
            $total_transaksi = $request->harga * $request->jumlah;
        }

        $update = [
            'product_id' => $request->product_id,
            'jumlah' => $request->jumlah,
            'harga' => $request->harga, // ambil dari database
            'total_transaksi' => $total_transaksi, // Simpan hasil perhitungan
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'supplier' => $request->supplier,
            'user_id' => $request->user_id ?? Auth::id(),
        ];

         // Perbarui stok produk setelah update
        $newJmlh = $request->jumlah;
        $newProductId = $request->product_id;

        // menyesuaikan stok jika produk_id berubah atau jumlahnya
        if ($oldProductId !== $newProductId) {
            // Kurangi stok dari produk lama
            $oldProduct = Products::find($oldProductId);
            if ($oldProduct) {
                $oldProduct->stok -= $oldJmlh;
                $oldProduct->save();
            }
            // Tambahkan stok ke produk baru
            $newProduct = Products::find($newProductId);
            if ($newProduct) {
                $newProduct->stok += $newJmlh;
                $newProduct->save();
            }
        } else {
            // Jika product_id sama, sesuaikan stok berdasarkan perubahan jumlah
            $product = Products::find($newProductId);
            if ($product) {
                $stokDiff = $newJmlh - $oldJmlh; // Hitung perbedaan jumlah
                $product->stok += $stokDiff;
                $product->save();
            }
        }

        return redirect()->route('transaction')
            ->with('success', 'Riwayat transaction berhasil diupdate!');
    }

    // Destroy - Menghapus data
    public function destroy($transaction): RedirectResponse
    {
         // Kurangi stok produk saat restock dihapus
        $product = Products::find($transaction->product_id);
        if ($product) {
            $product->stok -= $transaction->jumlah;
            // Pastikan stok tidak menjadi negatif
            if ($product->stok < 0) {
                $product->stok = 0;
            }
            $product->save();
        }

        $transaction->delete();
        return redirect()->route('transaction')
            ->with('success', 'riwayat transaction berhasil dihapus');
    }
}

