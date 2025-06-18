<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities'; // Define the table name if different

    protected $fillable = [
        'type',
        'description',
        'model_type',
        'model_id',
        'user_id',
        'details'
    ];
    // We don't need $fillable since this is just for display

    protected $casts = [
        'details' => 'array'
    ];

    // Optional: Add relationships if needed
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public $timestamps = true; // Ensure timestamps are managed

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
