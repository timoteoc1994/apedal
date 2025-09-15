<?php
namespace App\Jobs;

use App\Events\EliminacionSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Agregar esto como en el otro job

class CancelarSolicitudInmediata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $solicitudId;

    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    public function handle()
    {
        // Usar el mismo estilo de log que el job que funciona
        Log::info('Ejecutando cancelación de solicitud inmediata', [
            'solicitud_id' => $this->solicitudId
        ]);

        try {
            $solicitud = SolicitudRecoleccion::find($this->solicitudId);

            if (!$solicitud) {
                Log::warning('Solicitud no encontrada para cancelar', [
                    'solicitud_id' => $this->solicitudId
                ]);
                return;
            }

            Log::info('Solicitud encontrada', [
                'solicitud_id' => $this->solicitudId,
                'estado_actual' => $solicitud->estado
            ]);

            // Solo cancelar si aún está buscando reciclador
            if ($solicitud->estado === 'buscando_reciclador') {
                Log::info('Procediendo a cancelar solicitud', [
                    'solicitud_id' => $this->solicitudId
                ]);

                $solicitud->update([
                    'estado' => 'cancelado',
                    'comentarios' => 'Tiempo de espera agotado (Sistema automático de cancelación)'
                ]);

                Log::info('Solicitud cancelada automáticamente por timeout', [
                    'solicitud_id' => $this->solicitudId
                ]);

                // Cancelar automáticamente
                $ids_disponibles = json_decode($solicitud->ids_disponibles, true) ?: [];

                if (!empty($ids_disponibles)) {
                    Log::info('Enviando eliminación de solicitud', [
                        'solicitud_id' => $this->solicitudId,
                        'cantidad_recicladores' => count($ids_disponibles)
                    ]);
                    
                    foreach ($ids_disponibles as $id_disponible) {
                        try {
                            event(new EliminacionSolicitud($solicitud, $id_disponible));
                            
                            Log::info('Evento EliminacionSolicitud enviado', [
                                'solicitud_id' => $this->solicitudId,
                                'reciclador_id' => $id_disponible
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error al enviar evento EliminacionSolicitud', [
                                'solicitud_id' => $this->solicitudId,
                                'reciclador_id' => $id_disponible,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    Log::info('No hay recicladores para notificar eliminación', [
                        'solicitud_id' => $this->solicitudId
                    ]);
                }
            } else {
                Log::info('Solicitud no cancelada - estado diferente', [
                    'solicitud_id' => $this->solicitudId,
                    'estado_actual' => $solicitud->estado
                ]);
            }

            Log::info('Finalizando job CancelarSolicitudInmediata', [
                'solicitud_id' => $this->solicitudId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al cancelar solicitud', [
                'solicitud_id' => $this->solicitudId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}