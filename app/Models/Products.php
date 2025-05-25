<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'bisa_atau_tdk_diecer' => 'boolean', // casting boolean
        'harga_satuan' => 'decimal:2', // Casting untuk harga dengan 2 desimal
        'harga_eceran_per_unit' => 'decimal:2', // Casting untuk harga dengan 2 desimal
    ];

    // mendefinisikan hubungan (relationship) satu ke banyak(hasMany)
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'product_id', 'id');
        // 'Transaction::class' adalah nama model Transaction
        // 'product_id' adalah nama foreign key di tabel 'transaction'
        // 'id' adalah primary key di tabel 'products'
    }

    public function Restock(): HasMany
    {
        return $this->hasMany(Restock::class, 'product_id', 'id');
        // 'Restock::class' adalah nama model Transaction
    }
}
