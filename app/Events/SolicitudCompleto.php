<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SolicitudCompleto implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitudId;

    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    public function broadcastOn()
    {
        return [new Channel('solicitud_' . $this->solicitudId)];
    }

    public function broadcastWith()
    {
        return [
            'solicitud_id' => $this->solicitudId,
            'mensaje' => 'Solicitud completada'
        ];
    }
}
