<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model Activity untuk merekam aktivitas pada aplikasi
class Activity extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'activities';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'type',
        'description',
        'model_type',
        'model_id',
        'user_id',
        'details'
    ];

    // Konversi kolom details menjadi array otomatis
    protected $casts = [
        'details' => 'array'
    ];

    // Relasi ke model User (setiap aktivitas dimiliki oleh satu user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Aktifkan fitur timestamps (created_at & updated_at)
    public $timestamps = true;

    // Akses atribut waktu dalam format "x waktu yang lalu"
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
