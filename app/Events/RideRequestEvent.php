<?php

namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideRequestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideRequest;

    public function __construct(RideRequest $rideRequest)
    {
        $this->rideRequest = $rideRequest;
    }

    public function broadcastOn()
    {
        // Broadcast to driver's private channel
        return new Channel('user.' . $this->rideRequest->ride->driver_id . '.requests');
    }

    public function broadcastAs()
    {
        return 'request.received';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->rideRequest->id,
            'ride_id' => $this->rideRequest->ride_id,
            'passenger' => [
                'id' => $this->rideRequest->passenger->id,
                'name' => $this->rideRequest->passenger->name,
            ],
            'requested_seats' => $this->rideRequest->requested_seats,
            'message' => $this->rideRequest->message,
            'status' => $this->rideRequest->status,
            'created_at' => $this->rideRequest->created_at->toISOString(),
        ];
    }
}