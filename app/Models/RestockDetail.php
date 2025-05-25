<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestockDetail extends Model
{
    use HasFactory;

    protected $table = 'restock_details';

    protected $fillable = [
        'restock_id',
        'product_id',
        'jumlah',
        'harga_beli_per_unit',
        'subtotal_harga_beli',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli_per_unit' => 'decimal:2',
        'subtotal_harga_beli' => 'decimal:2',
    ];

    public function restock(): BelongsTo
    {
        return $this->belongsTo(Restock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class); 
    }
}
