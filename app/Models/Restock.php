<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Restock extends Model
{
    use HasFactory;
    //Mendefinisikan nama tabel
    protected $table = 'restock';

    //mendefinisikan kolom-kolom yang boleh diisi secara massal
    protected $fillable = [
        'product_id',
        'jumlah',
        'harga_beli',
        'total_harga',
        'tanggal_restock',
        'supplier',
        'user_id',
    ];

   protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'tanggal_restock' => 'datetime', // Casting untuk tanggal/waktu
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
