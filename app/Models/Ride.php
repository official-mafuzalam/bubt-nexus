<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'from_location',
        'to_location',
        'from_lat',
        'from_lng',
        'to_lat',
        'to_lng',
        'available_seats',
        'total_seats',
        'fare_per_seat',
        'status',
        'departure_time',
        'notes',
        'vehicle_type',
        'vehicle_number'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'from_lat' => 'decimal:8',
        'from_lng' => 'decimal:8',
        'to_lat' => 'decimal:8',
        'to_lng' => 'decimal:8',
        'fare_per_seat' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function requests()
    {
        return $this->hasMany(RideRequest::class);
    }

    public function confirmedPassengers()
    {
        return $this->hasMany(RideRequest::class)
            ->where('status', 'accepted')
            ->with('passenger');
    }

    public function locations()
    {
        return $this->hasMany(RideLocation::class)
            ->orderBy('recorded_at', 'desc');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)
            ->orderBy('created_at', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->where('available_seats', '>', 0)
            ->where('departure_time', '>', now());
    }

    public function scopeNearby($query, $lat, $lng, $radius = 20)
    {
        $earthRadius = 6371; // kilometers

        return $query->selectRaw(
            "id, driver_id, from_location, to_location, from_lat, from_lng, to_lat, to_lng, 
            available_seats, total_seats, fare_per_seat, status, departure_time, notes,
            ( $earthRadius * acos( cos( radians(?) ) * 
              cos( radians( from_lat ) ) * 
              cos( radians( from_lng ) - radians(?) ) + 
              sin( radians(?) ) * 
              sin( radians( from_lat ) ) ) ) AS distance",
            [$lat, $lng, $lat]
        )->having('distance', '<', $radius)
            ->orderBy('distance');
    }
}