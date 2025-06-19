<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany untuk relasi
use Tymon\JWTAuth\Contracts\JWTSubject; // Import JWTSubject jika menggunakan JWT


// Model User untuk autentikasi dan relasi data user
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasApiTokens;

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'name',
        'email',
        'password',
        'nomor_telepon',
    ];

    // Kolom yang disembunyikan saat serialisasi
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Tipe data kolom
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // JWT: Mengambil identifier user untuk token
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // JWT: Custom claims (kosong)
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relasi: User memiliki banyak transaksi
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }

    // Relasi: User memiliki banyak restock
    public function restocks(): HasMany
    {
        return $this->hasMany(Restock::class, 'user_id', 'id');
    }
}
