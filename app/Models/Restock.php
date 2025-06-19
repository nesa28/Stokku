<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model untuk data restock barang
class Restock extends Model
{
    use HasFactory;

    // Mendefinisikan nama tabel
    protected $table = 'restock';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'total_harga_beli',
        'tanggal_restock',
        'supplier',
        'user_id',
    ];

    // Tipe data kolom
    protected $casts = [
        'total_harga_beli' => 'decimal:2',
        'tanggal_restock' => 'date',
    ];

    // Relasi ke user (setiap restock dilakukan oleh satu user)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke detail restock (satu restock memiliki banyak detail)
    public function details(): HasMany
    {
        return $this->hasMany(RestockDetail::class, 'restock_id', 'id');
    }
}
