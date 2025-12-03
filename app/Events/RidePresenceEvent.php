<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RidePresenceEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $userName;
    public $rideId;
    public $action; // 'joined' or 'left'

    public function __construct($userId, $userName, $rideId, $action)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->rideId = $rideId;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('ride.' . $this->rideId . '.presence');
    }

    public function broadcastAs()
    {
        return 'presence.' . $this->action;
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
        ];
    }
}