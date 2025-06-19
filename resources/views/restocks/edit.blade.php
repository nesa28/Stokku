@extends('layouts.argon')

@section('content')
    <div class="absolute top-0 left-0 w-full h-40 bg-blue-500 rounded-bl-xl z-0 dark:hidden"></div>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <h1 class="text-xl font-bold text-slate-700 mb-6">Edit Restock</h1>

                <form action="{{ route('restocks.update', $restock->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Restock</label>
                            <input type="date" name="tanggal_restock"
                                value="{{ $restock->tanggal_restock->format('Y-m-d') }}" required
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Supplier</label>
                            <input type="text" name="supplier" value="{{ $restock->supplier }}"
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>

                    <hr class="my-6" />
                    <h2 class="text-lg font-semibold text-slate-700 mb-4">Produk Restock</h2>

                    <div id="produk-list" class="space-y-4 mb-6">
                        @foreach ($restock->details as $i => $detail)
                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 produk-item">
                                <input type="hidden" name="products[{{ $i }}][id]" value="{{ $detail->id }}">
                                <select name="products[{{ $i }}][product_id]" required
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $produk)
                                        <option value="{{ $produk->id }}" {{ $detail->product_id == $produk->id ? 'selected' : '' }}>
                                            {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="products[{{ $i }}][jumlah]" placeholder="Jumlah" min="1"
                                    value="{{ $detail->jumlah }}" required
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <input type="number" name="products[{{ $i }}][harga_beli_per_unit]"
                                    placeholder="Harga Beli/Unit" min="0" value="{{ $detail->harga_beli_per_unit }}" required
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <button type="button"
                                    class="remove-produk bg-red-600 hover:bg-red-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                                    Hapus
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="tambah-produk"
                        class="mb-6 bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm font-medium py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        + Tambah Produk
                    </button>

                    <div class="flex gap-4">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg transition duration-200 ease-in-out">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('restocks.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg transition duration-200 ease-in-out">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        let produkIndex = {{ count($restock->details) }};
        document.getElementById('tambah-produk').onclick = function () {
            const container = document.getElementById('produk-list');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 xl:grid-cols-4 gap-4 produk-item';

            newRow.innerHTML = `
                <input type="hidden" name="products[${produkIndex}][id]" value="">
                <select name="products[${produkIndex}][product_id]" required
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                    @endforeach
                </select>
                <input type="number" name="products[${produkIndex}][jumlah]" placeholder="Jumlah" min="1" required
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="number" name="products[${produkIndex}][harga_beli_per_unit]" placeholder="Harga Beli/Unit" min="0" required
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="button"
                    class="remove-produk bg-red-600 hover:bg-red-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                    Hapus
                </button>
            `;
            container.appendChild(newRow);
            produkIndex++;
        };

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-produk')) {
                e.target.closest('.produk-item').remove();
            }
        });
    </script>
@endsection
