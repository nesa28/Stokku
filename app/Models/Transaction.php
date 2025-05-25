<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;
    //Mendefinisikan nama tabel
    protected $table = 'transaction';

    //mendefinisikan kolom-kolom yang boleh diisi secara massal
    protected $fillable = [
        'product_id',
        'jumlah',
        'harga',
        'total_transaksi',
        'tanggal_transaksi',
        'pelanggan',
        'user_id',
    ];

   protected $casts = [
        'jumlah' => 'integer',
        'harga' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'tanggal_transaction' => 'datetime', // Casting untuk tanggal/waktu
    ];

    // mendefinisikan relasi banyak ke satu(BelongsTo)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id', 'id');
        // 'Product::class' adalah nama model Product
        // 'product_id' adalah foreign key di tabel 'restock'
        // 'id' adalah primary key di tabel 'products'
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
        // 'User::class' adalah nama model User
        // 'user_id' adalah foreign key di tabel 'restock'
        // 'id' adalah primary key di tabel 'users'
    }
}
