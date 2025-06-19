@extends('layouts.argon')

@section('content')
    {{-- Background effect --}}
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 py-6 z-10">
            <div class="relative z-10 px-6 py-6">

                <div class="bg-white shadow-xl rounded-2xl p-6 max-w-7xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-xl font-bold text-slate-700">Daftar Stok Produk</h1>
                        <a href="{{ route('products.create') }}"
                            class="mb-4 inline-block bg-blue-600 hover:bg-blue-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                            + Tambah Produk
                        </a>
                    </div>

                    <div class="mb-6">
                        <form action="{{ route('products.search') }}" method="GET" class="mb-6 flex items-center space-x-3">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari berdasarkan Kode Produk atau Nama Produk..."
                                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-400">

                            {{-- Eceran Filter Dropdown --}}
                            <select name="eceran_filter"
                                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">Semua Produk</option>
                                <option value="1" {{ request('eceran_filter') === '1' ? 'selected' : '' }}>Bisa Diecer</option>
                                <option value="0" {{ request('eceran_filter') === '0' ? 'selected' : '' }}>Tidak Bisa Diecer</option>
                            </select>

                            {{-- NEW: Sorting Dropdown --}}
                            <select name="sort_by"
                                    class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="latest" {{ request('sort_by') === 'latest' ? 'selected' : '' }}>Terbaru (Default)</option>
                                <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                                <option value="code_asc" {{ request('sort_by') === 'code_asc' ? 'selected' : '' }}>Kode Terkecil</option>
                                <option value="code_desc" {{ request('sort_by') === 'code_desc' ? 'selected' : '' }}>Kode Terbesar</option>
                                <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                                <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
                                <option value="stock_asc" {{ request('sort_by') === 'stock_asc' ? 'selected' : '' }}>Stok Terkecil</option>
                                <option value="stock_desc" {{ request('sort_by') === 'stock_desc' ? 'selected' : '' }}>Stok Terbesar</option>
                            </select>

                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                                Filter & Urutkan
                            </button>
                            @if(request('search') || request('eceran_filter') || request('sort_by'))
                                <a href="{{ route('products.index') }}"
                                   class="bg-gray-500 hover:bg-gray-600 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm border-collapse border border-gray-200">
                            <thead class="bg-gray-100 text-slate-700 font-semibold">
                                <tr>
                                    <th class="px-4 py-2 border-b">Kode Produk</th>
                                    <th class="px-4 py-2 border-b">Nama Produk</th>
                                    <th class="px-4 py-2 border-b">Satuan</th>
                                    <th class="px-4 py-2 border-b">Stok</th>
                                    <th class="px-4 py-2 border-b">Harga Satuan</th>
                                    <th class="px-4 py-2 border-b">Bisa Diecer?</th>
                                    <th class="px-4 py-2 border-b">Unit Eceran</th>
                                    <th class="px-4 py-2 border-b">Harga Eceran/Unit</th>
                                    <th class="px-4 py-2 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $produk)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 border-b">{{ $produk->user_product_code }}</td>
                                        <td class="px-4 py-2 border-b">{{ $produk->nama_produk }}</td>
                                        <td class="px-4 py-2 border-b">{{ $produk->satuan }}</td>
                                        <td class="px-4 py-2 border-b">{{ $produk->stok }}</td>
                                        <td class="px-4 py-2 border-b">Rp {{ number_format($produk->harga_satuan, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 border-b">{{ $produk->bisa_atau_tdk_diecer ? 'Ya' : 'Tidak' }}</td>
                                        <td class="px-4 py-2 border-b">{{ $produk->unit_eceran ?? '-' }}</td>
                                        <td class="px-4 py-2 border-b">
                                            @if($produk->harga_eceran_per_unit)
                                                Rp {{ number_format($produk->harga_eceran_per_unit, 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border-b space-x-2">
                                            <a href="{{ route('products.edit', $produk) }}"
                                               class="text-yellow-600 hover:text-yellow-700 font-medium text-xs">Edit</a>
                                            <form action="{{ route('products.destroy', $produk) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-700 font-medium text-xs"
                                                    onclick="return confirm('Yakin hapus produk ini?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-2 text-center text-gray-500">Tidak ada produk ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        @if ($products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                            {{ $products->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
