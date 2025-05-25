<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';

    protected $fillable = [
        'total_transaksi',
        'tanggal_transaksi',
        'pelanggan',
        'user_id',
    ];

    protected $casts = [
        'total_transaksi' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
    ];

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);// 'User::class' adalah nama model User
    }

    // Relasi ke Transaction Details (satu transaksi memiliki banyak detail)
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }
}
