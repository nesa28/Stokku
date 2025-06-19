<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk detail restock produk
class RestockDetail extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan
    protected $table = 'restock_details';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'restock_id',
        'product_id',
        'jumlah',
        'harga_beli_per_unit',
        'subtotal_harga_beli',
    ];

    // Tipe data kolom
    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli_per_unit' => 'decimal:2',
        'subtotal_harga_beli' => 'decimal:2',
    ];

    // Scope untuk pencarian berdasarkan restock_id
    public function scopeSearchByRestockId($query, $restockId)
    {
        return $query->where('restock_id', $restockId);
    }

    // Relasi ke tabel restock
    public function restock(): BelongsTo
    {
        return $this->belongsTo(Restock::class);
    }

    // Relasi ke tabel produk
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }
}
