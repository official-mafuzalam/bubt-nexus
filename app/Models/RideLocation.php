<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RideLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'user_id',
        'latitude',
        'longitude',
        'speed',
        'bearing',
        'accuracy'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'bearing' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'recorded_at' => 'datetime'
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}