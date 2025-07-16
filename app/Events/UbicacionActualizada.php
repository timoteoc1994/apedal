<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\AuthUser;

class UbicacionActualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $ubicacion;

    public function __construct($userId, $ubicacion)
    {
        $this->userId = $userId;
        $this->ubicacion = $ubicacion;
    }

    public function broadcastOn()
    {
        return [new Channel('ubicacion_' . $this->userId)];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->userId,
            'ubicacion' => $this->ubicacion
        ];
    }
}
