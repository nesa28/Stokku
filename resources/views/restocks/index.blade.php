@extends('layouts.argon')

@section('content')
<div class="absolute w-full bg-blue-500 dark:hidden min-h-75"></div>

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">

            <div class="bg-white shadow-xl rounded-2xl p-6 mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-xl font-bold text-slate-700">Daftar Restock</h1>
                    <a href="{{ route('restocks.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                        + Tambah Restock
                    </a>
                </div>

                <div class="mb-6">
                    <form action="{{ route('restocks.search') }}" method="GET" class="mb-6">
                        <div class="flex justify-between items-center mb-6">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari berdasarkan ID Restock atau Nama Produk/Supplier..." {{-- Updated
                                placeholder --}}
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
                                Cari
                            </button>
                            @if(request('search'))
                                <a href="{{ route('restocks.index') }}"
                                    class="bg-gray-500 hover:bg-gray-600 text-slate-600 text-sm font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out">
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
                                <th class="px-4 py-2 border-b">ID Restock</th>
                                <th class="px-4 py-2 border-b">Produk yang Direstock</th> {{-- Changed header --}}
                                <th class="px-4 py-2 border-b">Jumlah Produk</th>
                                <th class="px-4 py-2 border-b">Tanggal Restock</th>
                                <th class="px-4 py-2 border-b">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($restocks as $restock)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">
                                        {{ $loop->iteration + ($restocks->currentPage() - 1) * $restocks->perPage() }}</td>
                                    <td class="px-4 py-2 border-b">{{ $restock->id }}</td>
                                    <td class="px-4 py-2 border-b">
                                        {{-- Display the name of the first product and a count if there are more --}}
                                        @if ($restock->details->isNotEmpty())
                                            {{ $restock->details->first()->product->nama_produk ?? 'Produk tidak dikenal' }}
                                            @if ($restock->details->count() > 1)
                                                (+{{ $restock->details->count() - 1 }} lainnya)
                                            @endif
                                        @else
                                            Tidak ada produk
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $restock->details->sum('jumlah') }} unit</td> {{-- Sum of
                                    quantities for all products in this restock --}}
                                    <td class="px-4 py-2 border-b">{{ $restock->tanggal_restock?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-2 border-b space-x-2">
                                        <a href="{{ route('restocks.show', $restock) }}"
                                            class="text-blue-600 hover:text-blue-700 font-medium text-xs">Detail</a>
                                        <a href="{{ route('restocks.edit', $restock) }}"
                                            class="text-yellow-600 hover:text-yellow-700 font-medium text-xs">Edit</a>
                                        <form action="{{ route('restocks.destroy', $restock) }}" method="POST" class="inline">
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
                                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Tidak ada data restock yang
                                        ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $restocks->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
