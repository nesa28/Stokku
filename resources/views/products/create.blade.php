@extends('layouts.argon')
@section('content')
<div class="absolute top-0 left-0 w-full h-40 bg-blue-500 rounded-bl-xl z-0 dark:hidden"></div>

<main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
    <div class="relative z-10 px-6 py-6">
        <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 py-6 z-10">
            <div class="bg-white shadow-xl rounded-2xl p-6 max-w-7xl mx-auto">
                <h1 class="text-xl font-bold text-slate-700 mb-6">Daftar Stok Produk</h1> <!-- Form tambah produk -->
                <form action="{{ route('products.store') }}" method="POST"
                    class="grid grid-cols-1 gap-6 mb-8 xl:grid-cols-4"> @csrf <input type="text" name="nama_produk"
                        placeholder="Nama Produk" required
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <input type="text" name="satuan" placeholder="Satuan" required
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <input type="number" name="stok" placeholder="Stok" required
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <input type="number" name="harga_satuan" placeholder="Harga Satuan" required
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <select name="bisa_atau_tdk_diecer"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                        <option value="1">Bisa Diecer</option>
                        <option value="0">Tidak Bisa Diecer</option>
                    </select> <input type="text" name="unit_eceran" placeholder="Unit Eceran"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <input type="number" name="harga_eceran_per_unit" placeholder="Harga Eceran/Unit"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-slate text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        Tambah
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>
