<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CancelarSolicitudCiudadano implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitudId;
    public $motivo;
    public $comentario;

    public function __construct($solicitud, $motivo, $comentario = null)
    {
        $this->solicitudId = is_object($solicitud) ? $solicitud->id : $solicitud;
        $this->motivo = $motivo;
        $this->comentario = $comentario;
    }

    public function broadcastOn()
    {
        return [new Channel('cancelar_solicitud_ciudadano.' . $this->solicitudId)];
    }

    public function broadcastWith()
    {
        return [
            'solicitud_id' => $this->solicitudId,
            'motivo' => $this->motivo,
            'comentario' => $this->comentario,
            'tipoSolicitud' => 'cancelacion'
        ];
    }
}
