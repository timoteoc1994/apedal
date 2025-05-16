<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EliminacionSolicitud implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $solicitud;
    public $recicladorId;

    public function __construct($solicitud = null, $recicladorId = null)
    {
        try {
            Log::info('Constructor EliminacionSolicitud iniciado', [
                'solicitud_id' => $solicitud->id ?? 'Sin ID',
                'reciclador_id' => $recicladorId
            ]);

            $this->solicitud = $solicitud;
            $this->recicladorId = $recicladorId;

            Log::info('Constructor EliminacionSolicitud completado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en constructor EliminacionSolicitud', [
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
            Log::info('Definiendo canal de broadcast para eliminación', ['canal' => $canal]);
            return [
                new Channel($canal),
            ];
        } catch (\Exception $e) {
            Log::error('Error en broadcastOn de eliminación', [
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
            'recicladorId' => $this->recicladorId,
            'tipoSolicitud' => 'eliminacion' // Añadir este campo para facilitar identificación en el cliente
        ];
    }
}
