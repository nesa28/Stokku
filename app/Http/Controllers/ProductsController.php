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
            case 'id_asc':
                $products->orderBy('id', 'asc');
                break;
            case 'id_desc':
                $products->orderBy('id', 'desc');
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
            'stok'=> 'required|integer|min:0',
            'harga_satuan'=> 'required',
            'bisa_atau_tdk_diecer'=> 'boolean',
            'unit_eceran'=> 'nullable',
            'harga_eceran_per_unit'=> 'nullable',
        ]);

        $products = Products::create($request->all());

        // Catat aktivitas create
        $this->recordActivity('create', $products);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
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
