{{-- filepath: resources/views/products/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Produk</h1>
    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Nama Produk</label>
            <input type="text" name="nama_produk" class="form-control" value="{{ $product->nama_produk }}" required>
        </div>
        <div class="mb-3">
            <label>Satuan</label>
            <input type="text" name="satuan" class="form-control" value="{{ $product->satuan }}" required>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" value="{{ $product->stok }}" required>
        </div>
        <div class="mb-3">
            <label>Harga Satuan</label>
            <input type="number" name="harga_satuan" class="form-control" value="{{ $product->harga_satuan }}" required>
        </div>
        <div class="mb-3">
            <label>Bisa Diecer?</label>
            <select name="bisa_atau_tdk_diecer" class="form-control">
                <option value="1" {{ $product->bisa_atau_tdk_diecer ? 'selected' : '' }}>Ya</option>
                <option value="0" {{ !$product->bisa_atau_tdk_diecer ? 'selected' : '' }}>Tidak</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Unit Eceran</label>
            <input type="text" name="unit_eceran" class="form-control" value="{{ $product->unit_eceran }}">
        </div>
        <div class="mb-3">
            <label>Harga Eceran per Unit</label>
            <input type="number" name="harga_eceran_per_unit" class="form-control" value="{{ $product->harga_eceran_per_unit }}">
        </div>
        <button type="submit" class="btn btn-primary">Update Produk</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
