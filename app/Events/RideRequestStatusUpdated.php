<?php

namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideRequestStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideRequest;

    public function __construct(RideRequest $rideRequest)
    {
        $this->rideRequest = $rideRequest;
    }

    public function broadcastOn()
    {
        // Broadcast to passenger's private channel
        return new Channel('user.' . $this->rideRequest->passenger_id . '.requests');
    }

    public function broadcastAs()
    {
        return 'request.updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->rideRequest->id,
            'ride_id' => $this->rideRequest->ride_id,
            'status' => $this->rideRequest->status,
            'updated_at' => $this->rideRequest->updated_at->toISOString(),
        ];
    }
}