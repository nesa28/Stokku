<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restock extends Model
{
    use HasFactory;
    //Mendefinisikan nama tabel
    protected $table = 'restock';

    //mendefinisikan kolom-kolom yang boleh diisi secara massal
    protected $fillable = [
        'total_harga_beli',
        'tanggal_restock',
        'supplier',
        'user_id',
    ];

   protected $casts = [
        'total_harga_beli' => 'decimal:2',
        'tanggal_restock' => 'date',
    ];

    // mendefinisikan relasi banyak ke satu(BelongsTo)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke detail restock
    public function details(): HasMany
    {
        return $this->hasMany(RestockDetail::class, 'restock_id', 'id');
    }
}
