<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Activity;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

// Controller untuk manajemen produk
class ProductsController extends Controller
{
    // Mencatat aktivitas produk (create, update, dll)
    private function recordActivity($type, $product, $description = null)
    {
        Activity::create([
            'type' => $type,
            'description' => $description ?? ucfirst($type) . ' product: ' . $product->nama_produk,
            'model_type' => 'Product',
            'model_id' => $product->id,
            'user_id' => Auth::id(),
            'details' => [
                'product_name' => $product->nama_produk,
                'action' => $type,
                'timestamp' => now()
            ]
        ]);
    }

    // Menampilkan daftar produk dengan filter, pencarian, dan sorting
    public function index(Request $request): View
    {
        $eceranFilter = $request->input('eceran_filter');
        $searchValue = trim($request->input('search') ?? '');
        $sortBy = $request->input('sort_by', 'latest');

        $products = Products::where('user_id', auth()->id())
            ->when(!empty($searchValue), function ($query) use ($searchValue) {
                if (ctype_digit($searchValue)) {
                    $query->where('id', (int) $searchValue);
                } else {
                    $query->where('nama_produk', 'ILIKE', '%' . $searchValue . '%');
                }
            })
            ->when($eceranFilter !== null && $eceranFilter !== '', function ($query) use ($eceranFilter) {
                $query->where('bisa_atau_tdk_diecer', (bool) $eceranFilter);
            });

        // Sorting produk
        switch ($sortBy) {
            case 'oldest':
                $products->orderBy('created_at', 'asc');
                break;
            case 'code_asc':
                $products->orderBy('user_product_code', 'asc');
                break;
            case 'code_desc':
                $products->orderBy('user_product_code', 'desc');
                break;
            case 'name_asc':
                $products->orderBy('nama_produk', 'asc');
                break;
            case 'name_desc':
                $products->orderBy('nama_produk', 'desc');
                break;
            case 'stock_asc':
                $products->orderBy('stok', 'asc');
                break;
            case 'stock_desc':
                $products->orderBy('stok', 'desc');
                break;
            case 'latest':
            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        $products = $products->paginate(20);

        return view('products.index', compact('products'));
    }

    // Menampilkan form tambah produk
    public function create(): View
    {
        return view('products.create');
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required',
            'satuan' => 'required',
            'stok' => 'required|integer|min:0',
            'harga_satuan' => 'required',
            'bisa_atau_tdk_diecer' => 'boolean',

            'unit_eceran' => 'nullable|min:1',
            'harga_eceran_per_unit' => 'nullable|min:0',
        ]);

        $userId = Auth::id();

        // --- Generate user_product_code ---
        // Find the highest user_product_code for the current user
        $lastUserProductCode = Products::where('user_id', $userId)->max('user_product_code');
        // The new code will be the last one + 1, or 1 if it's the first product for this user
        $newUserProductCode = ($lastUserProductCode ?? 0) + 1;
        // --- End Generate user_product_code ---

        $product = Products::create(array_merge($validatedData, [
            'user_id' => $userId,
            'user_product_code' => $newUserProductCode, // <-- Add this
        ]));

        $this->recordActivity('create', $product);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }


    // Menampilkan detail produk tertentu
    public function show(Products $products): View
    {
        return view('products.show', compact('product'));
    }

    // Menampilkan form edit produk
    public function edit(Products $product): View
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }
        return view('products.edit', compact('product'));
    }

    // Memperbarui data produk
    public function update(Request $request, Products $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'nama_produk' => 'required',
            'satuan' => 'required',
            'stok' => 'required|integer|min:0',
            'harga_satuan' => 'required',
            'bisa_atau_tdk_diecer' => 'boolean',
            'unit_eceran' => 'nullable',
            'harga_eceran_per_unit' => 'nullable',
        ]);

        $product->update($validatedData);

        // Catat aktivitas update
        $this->recordActivity('update', $product);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    // Menghapus produk
    public function destroy(Products $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    // Pencarian produk (memanggil index agar DRY)
    public function search(Request $request): View
    {
        return $this->index($request);
    }
}
