@extends('layouts.argon') {{-- Assuming this is your main authenticated layout --}}

@section('content')
<div class="absolute top-0 left-0 w-full h-40 bg-blue-500 rounded-bl-xl z-0 dark:hidden"></div>

<main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
    <div class="relative z-10 px-6 py-6 max-w-7xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl p-6 mx-auto">
            <h1 class="text-xl font-bold text-slate-700 mb-6">Edit Produk {{ $product->nama_produk }}</h1>

            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT') {{-- Method spoofing for PUT request --}}

                {{-- Main Product Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="nama_produk" class="block text-sm font-medium text-slate-600 mb-1">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk"
                               value="{{ old('nama_produk', $product->nama_produk) }}" {{-- Pre-fill or old --}}
                               required
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('nama_produk') border-red-500 @enderror" />
                        @error('nama_produk')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="satuan" class="block text-sm font-medium text-slate-600 mb-1">Satuan Utama</label>
                        <input type="text" name="satuan" id="satuan"
                               value="{{ old('satuan', $product->satuan) }}"
                               required
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('satuan') border-red-500 @enderror" />
                        @error('satuan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="stok" class="block text-sm font-medium text-slate-600 mb-1">Stok (dalam Satuan Utama)</label>
                        <input type="number" name="stok" id="stok"
                               value="{{ old('stok', $product->stok) }}"
                               min="0" step="any" {{-- Use step="any" if stock is decimal --}}
                               required
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('stok') border-red-500 @enderror" />
                        @error('stok')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="harga_satuan" class="block text-sm font-medium text-slate-600 mb-1">Harga per Satuan Utama</label>
                        <input type="number" name="harga_satuan" id="harga_satuan"
                               value="{{ old('harga_satuan', $product->harga_satuan) }}"
                               step="0.01" min="0" required=
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('harga_satuan') border-red-500 @enderror" />
                        @error('harga_satuan')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="my-6" />

                {{-- Eceran Details Section --}}
                <h2 class="text-lg font-semibold text-slate-700 mb-4">Pengaturan Eceran</h2>

                <div class="mb-4">
                    <label for="bisa_atau_tdk_diecer" class="block text-sm font-medium text-slate-600 mb-1">
                        <input type="checkbox" name="bisa_atau_tdk_diecer" id="bisa_atau_tdk_diecer" value="1"
                               {{ old('bisa_atau_tdk_diecer', $product->bisa_atau_tdk_diecer) ? 'checked' : '' }} {{-- Pre-fill or old --}}
                               class="mr-2">
                        Bisa Dijual Eceran?
                    </label>
                    @error('bisa_atau_tdk_diecer')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div id="eceran-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6"
                     style="{{ old('bisa_atau_tdk_diecer', $product->bisa_atau_tdk_diecer) ? 'display: grid;' : 'display: none;' }}"> {{-- Initial display state based on existing data or old value --}}
                    <div>
                        <label for="unit_eceran" class="block text-sm font-medium text-slate-600 mb-1">Nama Unit Eceran (contoh: Pcs, Batang)</label>
                        <input type="text" name="unit_eceran" id="unit_eceran"
                               value="{{ old('unit_eceran', $product->unit_eceran) }}"
                               placeholder="Unit Eceran"
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:ring-2 focus:ring-blue-400
                               @error('unit_eceran') border-red-500 @enderror">
                        @error('unit_eceran')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="harga_eceran_per_unit" class="block text-sm font-medium text-slate-600 mb-1">Harga Eceran per Unit</label>
                        <input type="number" name="harga_eceran_per_unit" id="harga_eceran_per_unit"
                               value="{{ old('harga_eceran_per_unit', $product->harga_eceran_per_unit) }}"
                               step="0.01" min="0" placeholder="Harga Eceran/Unit"
                               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-400
                               @error('harga_eceran_per_unit') border-red-500 @enderror">
                        @error('harga_eceran_per_unit')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Empty div to maintain grid layout for 3 fields in a 2-column grid --}}
                    <div></div>
                </div>

                <div class="flex gap-4 justify-end mt-6">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-slate-700 text-sm font-semibold py-2 px-6 rounded-lg">
                        Update Produk
                    </button>
                    <a href="{{ route('products.index') }}"
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
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('bisa_atau_tdk_diecer');
        const eceranFields = document.getElementById('eceran-fields');

        function toggleEceranFields() {
            if (checkbox.checked) {
                eceranFields.style.display = 'grid'; // Maintain grid layout
                // Make fields required (HTML5 validation) if checkbox is checked
                eceranFields.querySelectorAll('input, select').forEach(field => {
                    // Only apply 'required' if the field has a name and is not the CSRF token
                    if (field.name && field.name !== '_token') {
                        field.setAttribute('required', 'required');
                    }
                });
            } else {
                eceranFields.style.display = 'none';
                // Remove required attribute if checkbox is unchecked
                eceranFields.querySelectorAll('input, select').forEach(field => {
                    field.removeAttribute('required');
                });
            }
        }

        checkbox.addEventListener('change', toggleEceranFields);
        toggleEceranFields(); // Call on load to set initial state based on old value or existing product data
    });
</script>
@endpush
