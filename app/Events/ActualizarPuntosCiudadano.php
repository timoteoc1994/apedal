<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActualizarPuntosCiudadano implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
     public $userId;
    public $puntos;

    public function __construct($userId, $puntos)
    {
        $this->userId = $userId;
        $this->puntos = $puntos;
    }
    
    public function broadcastOn(): array
    {
        return [
            new Channel('puntos'. $this->userId),
        ];
    }
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'puntos' => $this->puntos,
        ];
    }
}
