<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Model untuk data barang
class Products extends Model
{
    use HasFactory;
    //Mendefinisikan nama tabel
    protected $table = 'products';

    //mendefinisikan kolom-kolom yang boleh diisi secara massal
    protected $fillable = [
        'nama_produk',
        'satuan',
        'stok',
        'harga_satuan',
        'bisa_atau_tdk_diecer',
        'unit_eceran',
        'harga_eceran_per_unit',
        'user_id', // Menambahkan user_id untuk relasi dengan pengguna
    ];

    protected $casts = [
        'bisa_atau_tdk_diecer' => 'boolean', // casting boolean
        'harga_satuan' => 'decimal:2', // Casting untuk harga dengan 2 desimal
        'harga_eceran_per_unit' => 'decimal:2', // Casting untuk harga dengan 2 desimal
    ];

    // Hubungan (relationship) dengan detail transaksi (satu produk bisa ada di banyak detail transaksi)
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'product_id', 'id');
        // 'TransactionDetail::class' adalah model untuk detail transaksi
        // 'product_id' adalah foreign key di tabel 'transaction_details'
        // 'id' adalah primary key di tabel 'products'
    }

    // Hubungan (relationship) dengan detail restock (satu produk bisa ada di banyak detail restock)
    public function restockDetails(): HasMany
    {
        return $this->hasMany(RestockDetail::class, 'product_id', 'id');
        // 'RestockDetail::class' adalah model untuk detail restock
    }
}
