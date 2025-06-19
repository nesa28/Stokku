@extends('layouts.argon') {{-- Assuming you're using layouts.argon for authenticated pages --}}

@section('content')
    {{-- Background effect --}}
    <div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">

            <div class="bg-white shadow-xl rounded-2xl p-6 mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-xl font-bold text-slate-700">Detail Restock #{{ $restock->id }}</h1>
                    <a href="{{ route('restocks.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        Kembali ke Daftar Restock
                    </a>
                </div>

                {{-- Restock Summary --}}
                <div class="mb-8 p-4 border rounded-lg bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>
                            <p class="font-semibold">Tanggal Restock:</p>
                            <p>{{ $restock->tanggal_restock?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Supplier:</p>
                            <p>{{ $restock->supplier ?? 'Tidak Diketahui' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Dicatat Oleh:</p>
                            <p>{{ $restock->user->name ?? 'Pengguna Tidak Dikenal' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Total Harga Beli:</p>
                            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($restock->total_harga_beli, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Products in Restock --}}
                <h2 class="text-lg font-bold text-slate-700 mb-4">Produk dalam Restock</h2>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full text-left text-sm border-collapse border border-gray-200">
                        <thead class="bg-gray-100 text-slate-700 font-semibold">
                            <tr>
                                <th class="px-4 py-2 border-b">No.</th>
                                <th class="px-4 py-2 border-b">Nama Produk</th>
                                <th class="px-4 py-2 border-b">Jumlah (Unit)</th>
                                <th class="px-4 py-2 border-b">Harga Beli per Unit</th>
                                <th class="px-4 py-2 border-b">Subtotal Beli</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($restock->details as $index => $detail)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 border-b">{{ $detail->product->nama_produk ?? 'Produk Dihapus' }}</td>
                                    <td class="px-4 py-2 border-b">{{ $detail->jumlah }}</td>
                                    <td class="px-4 py-2 border-b">Rp {{ number_format($detail->harga_beli_per_unit, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 border-b">Rp {{ number_format($detail->subtotal_harga_beli, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-center text-gray-500">Tidak ada produk dalam restock ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons (e.g., Edit, Delete) --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('restocks.edit', $restock) }}"
                       class="bg-yellow-500 hover:bg-yellow-600 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        Edit Restock
                    </a>
                    <form action="{{ route('restocks.destroy', $restock) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-slate-700 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out"
                                onclick="return confirm('Yakin ingin menghapus restock ini? Stok produk akan dikembalikan.')">
                            Hapus Restock
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>
@endsection
