<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\View\View; // Digunakan untuk method yang mengembalikan view


class ProductsController extends Controller
{
    // Index - Menampilkan semua data
    public function index(): View
    {
        $products = Products::lastest()-> paginate(20); // diurutkan berdasarkan yang terbaru, dan paginate 20 per halaman
        return view('products.index', compact('products'));
    }

    // Create - Menampilkan form tambah data
    public function create(): View
    {
        return view('products.create');
    }

    // Store - Menyimpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required', // memastikan tidak kosong
            'satuan' => 'required',
            'stok'=> 'required|integer|min:0',
            'harga_satuan'=> 'required',
            'bisa_atau_tdk_diecer'=> 'boolean',
            'unit_eceran'=> 'nullable',
            'harga_eceran_per_unit'=> 'nullable',
        ]);

        // Cara 1: Eloquent create
        $products = Products::create($request->all());

        return redirect()->route('products')
            ->with('success', 'Produk berhasil ditambahkan!');
    }

     //Menampilkan detail produk tertentu.
     public function show(Products $products): View
    {
        return view('products.show', compact('product'));
    }

    // Edit - Menampilkan form edit
    public function edit($id): View
    {
        $products = Products::find($id);
        return view('product.edit', compact('products'));
    }

    // Update - Memperbarui data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required',
            'satuan' => 'required',
            'stok'=> 'required|integer|min:0',
            'harga_satuan'=> 'required',
            'bisa_atau_tdk_diecer'=> 'boolean',
            'unit_eceran'=> 'nullable',
            'harga_eceran_per_unit'=> 'nullable',
        ]);

        $update = [
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
            'stok'=> $request->stok,
            'harga_satuan'=> $request->harga_satuan,
            'bisa_atau_tdk_diecer'=> $request->bisa_atau_tdk_diecer,
            'unit_eceran'=> $request->unit_eceran,
            'harga_eceran_per_unit'=>$request->harga_eceran_per_unit,
        ];

        Products::whereId($id)->update($update);
        return redirect()->route('products')
            ->with('success', 'Produk berhasil diupdate!');
    }

    // Destroy - Menghapus data
    public function destroy($id)
    {
        $products = Products::find($id);
        $products->delete();
        return redirect()->route('products')
            ->with('success', 'Produk berhasil dihapus');
    }
}
