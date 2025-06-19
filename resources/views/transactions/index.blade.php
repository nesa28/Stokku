@extends('layouts.argon') {{-- Assuming you have a layout file --}}

@section('content')
<div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">

            <div class="bg-white shadow-xl rounded-2xl p-6 mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-xl font-bold text-slate-700">Daftar Transaksi</h1>
                    <a href="{{ route('transactions.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        + Tambah Transaksi
                    </a>
                </div>
                <div class="mb-6">
                    <form action="{{ route('transactions.search') }}" method="GET">
                        <div class="flex justify-between items-center mb-6">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari berdasarkan ID Transaksi, Nama Produk, atau Kasir..."
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm max-w-xs w-full focus:outline-none focus:ring-2 focus:ring-blue-400" />

                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition">
                                Cari
                            </button>

                            @if(request('search'))
                                <a href="{{ route('transactions.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition">
                                    Tampilkan Semua
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm border-collapse border border-gray-200">
                        <thead class="bg-gray-100 text-slate-700 font-semibold">
                            <tr>
                                <th class="px-4 py-2 border-b">No</th>
                                <th class="px-4 py-2 border-b">ID Transaksi</th>
                                <th class="px-4 py-2 border-b">Produk Terjual</th> {{-- Changed header for products --}}
                                <th class="px-4 py-2 border-b">Jumlah Produk</th> {{-- Changed header for amount --}}
                                <th class="px-4 py-2 border-b">Tipe Penjualan</th> {{-- Added for sell type --}}
                                <th class="px-4 py-2 border-b">Tanggal Transaksi</th>
                                <th class="px-4 py-2 border-b">Kasir</th> {{-- Added for user --}}
                                <th class="px-4 py-2 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    {{-- Sequential number for the current page --}}
                                    <td class="px-4 py-2 border-b">{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                                    <td class="px-4 py-2 border-b">{{ $transaction->id }}</td>
                                    <td class="px-4 py-2 border-b">
                                        {{-- Display the name of the first product and a count if there are more --}}
                                        @if ($transaction->details->isNotEmpty())
                                            {{ $transaction->details->first()->product->nama_produk ?? 'Produk tidak dikenal' }}
                                            @if ($transaction->details->count() > 1)
                                                (+{{ $transaction->details->count() - 1 }} lainnya)
                                            @endif
                                        @else
                                            Tidak ada produk
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $transaction->details->sum('jumlah') }} unit</td> {{-- Sum of quantities for all products in this transaction --}}
                                    <td class="px-4 py-2 border-b">
                                        {{-- Assuming sell type is stored in transaction details --}}
                                        @if ($transaction->details->isNotEmpty())
                                            {{-- If all details have the same sell type, display it --}}
                                            {{-- Otherwise, you might need more complex logic or show "Campuran" --}}
                                            {{ $transaction->details->first()->tipe_penjualan ?? '-' }} {{-- Adjust 'tipe_penjualan' if column name is different --}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $transaction->tanggal_transaksi?->format('d M Y H:i') ?? '-' }}</td> {{-- Display date and time --}}
                                    <td class="px-4 py-2 border-b">{{ $transaction->user->name ?? '-' }}</td> {{-- Display cashier name --}}
                                    <td class="px-4 py-2 border-b space-x-2">
                                        <a href="{{ route('transactions.show', $transaction) }}"
                                           class="text-blue-600 hover:text-blue-700 font-medium text-xs">Detail</a>
                                        <a href="{{ route('transactions.edit', $transaction) }}"
                                           class="text-yellow-600 hover:text-yellow-700 font-medium text-xs">Edit</a>
                                        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium text-xs"
                                                    onclick="return confirm('Yakin hapus data ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-2 text-center text-gray-500">Tidak ada data transaksi yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }} {{-- Pagination links --}}
                </div>
            </div>
        </div>
    </main>
@endsection
