<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SolicitudAgendada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitud;
    public $recicladorId;

    public function __construct($solicitud = null, $recicladorId = null)
    {
        try {
            Log::info('Constructor SolicitudAgendada iniciado', [
                'solicitud_id' => $solicitud->id ?? 'Sin ID',
                'reciclador_id' => $recicladorId
            ]);

            $this->solicitud = $solicitud;
            $this->recicladorId = $recicladorId;

            Log::info('Constructor SolicitudAgendada completado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en constructor SolicitudAgendada', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function broadcastOn()
    {
        try {
            $canal = 'reciclador-' . $this->recicladorId;
            Log::info('Definiendo canal de broadcast', ['canal' => $canal]);
            return [
                new Channel($canal),
            ];
        } catch (\Exception $e) {
            Log::error('Error en broadcastOn', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function broadcastWith()
    {
        return [
            'solicitud' => $this->solicitud,
            'recicladorId' => $this->recicladorId
        ];
    }
}
