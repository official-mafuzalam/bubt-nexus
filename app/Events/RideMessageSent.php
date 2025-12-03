<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideId;
    public $message;

    public function __construct($rideId, Message $message)
    {
        $this->rideId = $rideId;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('ride.' . $this->rideId . '.chat');
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'ride_id' => $this->message->ride_id,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
            'message' => $this->message->message,
            'type' => $this->message->type,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}