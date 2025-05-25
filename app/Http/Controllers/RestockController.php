<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Restock;
use App\Models\User;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse; // Digunakan untuk method yang mengembalikan redirect

class RestockController extends Controller
{
   // Index - Menampilkan semua data
    public function index(): View
    {
        $restocks = Restock::with(['product', 'user'])->latest()->paginate(10); // Memuat relasi 'product' dan 'user' untuk ditampilkan
        return view('restocks.index', compact('restocks'));
    }

    // Create - Menampilkan form tambah data
    public function create(): View
    {
        $products = Products::all(); // Ambil semua produk untuk dropdown di form
        $users = User::all();
        return view('restock.create', compact('products', 'users'));
    }

    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'product_id' =>'required|exists:products,id', // product_id harus ada di tabel products
            'jumlah'=>'required',
            'harga_beli'=>'required',
            'tanggal_restock'=>'required|date',
            'supplier'=>'nullable',
            'user_id'=>'nullable|exists:users,id',// user_id harus ada di tabel users
        ]);

        // Hitung total harga beli jika harga_beli ada
        $total_harga = null;
        if ($request->filled('harga_beli') && $request->filled('jumlah')) {
            $total_harga = $request->harga_beli * $request->jumlah;
        }

       // Buat record restock baru di database
        $restock = Restock::create([
            'product_id' => $request->product_id,
            'jumlah' => $request->jumlah,
            'harga_beli' => $request->harga_beli,
            'total_harga' => $total_harga, // Simpan hasil perhitungan
            'tanggal_restock' => $request->tanggal_restock,
            'supplier' => $request->supplier,
            'user_id' => $request->user_id ?? Auth::id(), // Jika user_id tidak diisi, pakai user yang login
        ]);

        //Perbarui stok produk setelah restock
        $product = Products::find($request->product_id);
        if ($product) {
            $product->stok += $request->jumlah;
            $product->save();
        }

        return redirect()->route('restock')
            ->with('success', 'Riwayat restock berhasil ditambahkan!');
    }

    //Menampilkan detail riwayat restock tertentu.
     public function show(Restock $restock): View
    {
        return view('restocks.show', compact('restock'));
    }

    // Edit - Menampilkan form edit
    public function edit($id): View
    {
        // Ambil semua produk dan user untuk dropdown di form edit
        $products = Products::all();
        $users = User::all();
        $restock = Restock::find($id);
        return view('restock.edit', compact('restock'));
    }

    // Update - Memperbarui data
    public function update(Request $request,Restock $restock): RedirectResponse
    {
        // Simpan jumlah lama sebelum update untuk perhitungan stok
        $oldJmlh = $restock->jumlah;
        $oldProductId = $restock->product_id;

        $request->validate([
            'product_id' =>'required|exists:products,id',
            'jumlah'=>'required',
            'harga_beli'=>'required',
            'tanggal_restock'=>'required|date',
            'supplier'=>'nullable',
            'user_id'=>'nullable|exists:users,id',
        ]);

        // Hitung total harga beli jika harga_beli ada
        $total_harga = null;
        if ($request->filled('harga_beli') && $request->filled('jumlah')) {
            $total_harga = $request->harga_beli * $request->jumlah;
        }

        $update = [
            'product_id' => $request->product_id,
            'jumlah' => $request->jumlah,
            'harga_beli' => $request->harga_beli,
            'total_harga' => $total_harga, // Simpan hasil perhitungan
            'tanggal_restock' => $request->tanggal_restock,
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

        return redirect()->route('restock')
            ->with('success', 'Riwayat restock berhasil diupdate!');
    }

    // Destroy - Menghapus data
    public function destroy($restock): RedirectResponse
    {
         // Kurangi stok produk saat restock dihapus
        $product = Products::find($restock->product_id);
        if ($product) {
            $product->stok -= $restock->jumlah;
            // Pastikan stok tidak menjadi negatif
            if ($product->stok < 0) {
                $product->stok = 0;
            }
            $product->save();
        }

        $restock->delete();
        return redirect()->route('restock')
            ->with('success', 'riwayat restock berhasil dihapus');
    }
}

