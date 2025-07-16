<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $locationData;
    public $recicladorId;

    public function __construct($locationData, $recicladorId)
    {
        $this->locationData = $locationData;
        $this->recicladorId = $recicladorId;
    }

    public function broadcastOn()
    {
        return new Channel('location-reciclador-' . $this->recicladorId);
    }

    public function broadcastAs()
    {
        return 'LocationUpdate';
    }

    public function broadcastWith()
    {
        return [
            'location' => $this->locationData,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
