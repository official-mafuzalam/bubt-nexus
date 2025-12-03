<?php

namespace App\Events;

use App\Models\RideLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideId;
    public $location;

    public function __construct($rideId, RideLocation $location)
    {
        $this->rideId = $rideId;
        $this->location = $location;
    }

    public function broadcastOn()
    {
        return new Channel('ride.' . $this->rideId . '.locations');
    }

    public function broadcastAs()
    {
        return 'location.updated';
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->location->user_id,
            'user_name' => $this->location->user->name,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'speed' => $this->location->speed,
            'bearing' => $this->location->bearing,
            'accuracy' => $this->location->accuracy,
            'recorded_at' => $this->location->recorded_at->toISOString(),
        ];
    }
}