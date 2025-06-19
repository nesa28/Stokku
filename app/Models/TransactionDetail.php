<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk detail transaksi penjualan
class TransactionDetail extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'transaction_details';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'transaction_id',
        'product_id',
        'jumlah',
        'jenis_penjualan',
        'harga_per_unit',
        'subtotal',
    ];

    // Tipe data kolom
    protected $casts = [
        'jumlah' => 'integer',
        'harga_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relasi ke transaksi (setiap detail milik satu transaksi)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke produk (setiap detail milik satu produk)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }
}
