@extends('layouts.argon')

@section('content')
<div class="absolute top-0 left-0 w-full h-40 bg-blue-500 rounded-bl-xl z-0 dark:hidden"></div>

<main class="relative h-full max-h-screen xl:ml-68 rounded-xl">
    <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <h1 class="text-xl font-bold text-slate-700 mb-6">Tambah Transaksi</h1>

            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="tanggal_transaksi" class="block text-sm font-medium text-slate-600 mb-1">Tanggal Transaksi</label>
                        <input type="date" name="tanggal_transaksi" id="tanggal_transaksi"
                               value="{{ old('tanggal_transaksi', date('Y-m-d')) }}" {{-- Pre-fill with current date, retain old value --}}
                               required
                               class="border rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('tanggal_transaksi') border-red-500 @enderror" /> {{-- Add error styling --}}
                        @error('tanggal_transaksi')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pelanggan" class="block text-sm font-medium text-slate-600 mb-1">Pelanggan</label>
                        <input type="text" name="pelanggan" id="pelanggan" value="{{ old('pelanggan') }}"
                               class="border rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('pelanggan') border-red-500 @enderror" />
                        @error('pelanggan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="my-6" />
                <h2 class="text-lg font-semibold text-slate-700 mb-4">Produk Terjual</h2>

                <div id="produk-list" class="space-y-4 mb-6">
                    {{-- Initial Product Item (index 0) --}}
                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 produk-item p-3 border rounded-md bg-gray-50"> {{-- Changed to 4 columns --}}
                        <div>
                            <label for="product_id_0" class="block text-sm font-medium text-slate-600 mb-1">Produk</label>
                            <select name="products[0][product_id]" id="product_id_0" required
                                    class="border rounded-lg px-3 py-2 text-sm w-full
                                    @error('products.0.product_id') border-red-500 @enderror">
                                <option value="">Pilih Produk</option> {{-- Ensure default value is empty string --}}
                                @foreach($products as $produk)
                                    <option value="{{ $produk->id }}" {{ old('products.0.product_id') == $produk->id ? 'selected' : '' }}>
                                        {{ $produk->nama_produk }} (Stok: {{ $produk->stock }})
                                    </option>
                                @endforeach
                            </select>
                            @error('products.0.product_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jumlah_0" class="block text-sm font-medium text-slate-600 mb-1">Jumlah</label>
                            <input type="number" name="products[0][jumlah]" id="jumlah_0" min="1" placeholder="Jumlah" required
                                   value="{{ old('products.0.jumlah', 1) }}" {{-- Pre-fill with 1, retain old value --}}
                                   class="border rounded-lg px-3 py-2 text-sm w-full
                                   @error('products.0.jumlah') border-red-500 @enderror" />
                            @error('products.0.jumlah')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- REMOVED: harga_jual_per_unit input as it's not used by your backend logic --}}

                        <div>
                            <label for="jenis_penjualan_0" class="block text-sm font-medium text-slate-600 mb-1">Jenis Penjualan</label>
                            <select name="products[0][jenis_penjualan]" id="jenis_penjualan_0" required
                                    class="border rounded-lg px-3 py-2 text-sm w-full
                                    @error('products.0.jenis_penjualan') border-red-500 @enderror">
                                <option value="">Pilih Jenis Penjualan</option>
                                <option value="satuan" {{ old('products.0.jenis_penjualan') == 'satuan' ? 'selected' : '' }}>Satuan</option>
                                <option value="eceran" {{ old('products.0.jenis_penjualan') == 'eceran' ? 'selected' : '' }}>Eceran</option>
                            </select>
                            @error('products.0.jenis_penjualan')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end justify-center">
                             <button type="button"
                                class="remove-produk bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-lg w-full">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" id="tambah-produk"
                    class="mb-6 bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm py-2 px-4 rounded-lg">
                    + Tambah Produk
                </button>

                {{-- Overall products array error (if no products are submitted) --}}
                @error('products')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div class="flex gap-4">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-6 rounded-lg">
                        Simpan Transaksi
                    </button>
                    <a href="{{ route('transactions.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let produkIndex = 1; // Start from 1 because the initial HTML product row uses index 0

        const productListContainer = document.getElementById('produk-list');
        const addProductButton = document.getElementById('tambah-produk');

        // Function to create a new product item HTML
        function createProductItem(index) {
            return `
                <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 produk-item p-3 border rounded-md bg-gray-50">
                    <div>
                        <label for="product_id_${index}" class="block text-sm font-medium text-slate-600 mb-1">Produk</label>
                        <select name="products[${index}][product_id]" id="product_id_${index}" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
                            <option value="">Pilih Produk</option>
                            @foreach($products as $produk)
                                <option value="{{ $produk->id }}">{{ $produk->nama_produk }} (Stok: {{ $produk->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="jumlah_${index}" class="block text-sm font-medium text-slate-600 mb-1">Jumlah</label>
                        <input type="number" name="products[${index}][jumlah]" id="jumlah_${index}" min="1" placeholder="Jumlah" value="1" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full" />
                    </div>
                    <div>
                        <label for="jenis_penjualan_${index}" class="block text-sm font-medium text-slate-600 mb-1">Jenis Penjualan</label>
                        <select name="products[${index}][jenis_penjualan]" id="jenis_penjualan_${index}" required
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
                            <option value="">Pilih Jenis Penjualan</option>
                            <option value="satuan">Satuan</option>
                            <option value="eceran">Eceran</option>
                        </select>
                    </div>
                    <div class="flex items-end justify-center">
                        <button type="button"
                            class="remove-produk bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-lg w-full">
                            Hapus
                        </button>
                    </div>
                </div>
            `;
        }

        addProductButton.addEventListener('click', function () {
            const newRowHtml = createProductItem(produkIndex).trim();
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newRowHtml;
            productListContainer.appendChild(tempDiv.firstChild);
            produkIndex++;
        });

        productListContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-produk')) {
                // Ensure at least one product row remains if 'products' is required
                if (productListContainer.children.length > 1) {
                    e.target.closest('.produk-item').remove();
                } else {
                    alert('Minimal harus ada satu produk untuk transaksi.');
                }
            }
        });
    });
</script>
@endpush
