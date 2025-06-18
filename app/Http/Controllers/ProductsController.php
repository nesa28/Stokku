<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Activity;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProductsController extends Controller
{

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

    public function index(Request $request): View
    {
        $eceranFilter = $request->input('eceran_filter');
        $searchValue = trim($request->input('search') ?? '');
        $sortBy = $request->input('sort_by', 'latest'); // Ambil parameter sort_by, default 'latest'

        $products = Products::where('user_id', auth()->id()) // Filter kepemilikan user
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

        // Apply sorting logic
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
            case 'latest': // Default
            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        $products = $products->paginate(20); // Gunakan pagination

        return view('products.index', compact('products')); // Sesuaikan dengan nama view Anda
    }

    // Create - Menampilkan form tambah data
    public function create(): View
    {
        return view('products.create');
    }

    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_produk' => 'required', // memastikan tidak kosong
            'satuan' => 'required',
            'stok' => 'required|integer|min:0',
            'harga_satuan' => 'required',
            'bisa_atau_tdk_diecer' => 'boolean',
            //jika bisa diecer, maka unit eceran harus diisi
            'unit_eceran' => 'required_if:bisa_atau_tdk_diecer,1|nullable|string|max:255', // jika bisa diecer, maka unit eceran harus diisi
            'harga_eceran_per_unit' => 'required_if:bisa_atau_tdk_diecer,1|nullable|numeric|min:0', // jika bisa diecer, maka harga eceran per unit harus diisi

        ]);

        Products::create(array_merge($validatedData, [
            'user_id' => auth()->id(), // <-- Add this line
        ]));

        // Create product
        $product = Products::create($request->all());

        // Record activity
        $this->recordActivity('create', $product);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

    //Menampilkan detail produk tertentu.
    public function show(Products $products): View
    {
        return view('products.show', compact('product'));
    }

    // Edit - Menampilkan form edit
    public function edit(Products $product): View
    {
        if ($product->user_id !== auth()->id()) {
            abort(403); // Forbidden access
        }
        return view('products.edit', compact('product'));
    }

    // Update - Memperbarui data
    public function update(Request $request, Products $product)
    {
        // Authorize: ensure only the owner can update
        if ($product->user_id !== auth()->id()) {
            abort(403); // Forbidden access
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

        // Record activity
        $this->recordActivity('update', $product);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    // Destroy - Menghapus data
    public function destroy(Products $product)
    {
        // Authorize: ensure only the owner can delete
        if ($product->user_id !== auth()->id()) {
            abort(403); // Forbidden access
        }
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    public function search(Request $request): View
    {
        return $this->index($request); // Cukup panggil metode index untuk DRY (Don't Repeat Yourself)
    }
}
