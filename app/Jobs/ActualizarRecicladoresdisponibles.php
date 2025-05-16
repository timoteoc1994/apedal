<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SolicitudRecoleccion;
use App\Events\NuevaSolicitudInmediata;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ActualizarRecicladoresdisponibles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $solicitudId;
    protected $intentos;
    protected $maxIntentos;
    protected $radioKm;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($solicitudId, $intentos = 0, $maxIntentos = 4, $radioKm = 3)
    {
        $this->solicitudId = $solicitudId;
        $this->intentos = $intentos;
        $this->maxIntentos = $maxIntentos;
        $this->radioKm = $radioKm;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Ejecutando búsqueda de recicladores adicionales', [
            'solicitud_id' => $this->solicitudId,
            'intento' => $this->intentos + 1,
            'max_intentos' => $this->maxIntentos
        ]);

        $solicitud = SolicitudRecoleccion::find($this->solicitudId);

        // Verificar si la solicitud aún existe y sigue en estado de búsqueda
        if (!$solicitud || $solicitud->estado !== 'buscando_reciclador') {
            Log::info('Búsqueda de recicladores cancelada: solicitud no disponible o ya asignada', [
                'solicitud_id' => $this->solicitudId,
                'intento' => $this->intentos + 1
            ]);
            return;
        }

        // Obtener los IDs actuales de recicladores disponibles
        $idsActuales = json_decode($solicitud->ids_disponibles, true) ?: [];

        // Buscar nuevos recicladores cercanos usando Redis
        $nuevosRecicladores = $this->encontrarNuevosRecicladores(
            $solicitud->latitud,
            $solicitud->longitud,
            $this->radioKm,
            10,
            $idsActuales
        );

        if (!$nuevosRecicladores->isEmpty()) {
            // Extraer nuevos IDs de recicladores
            $nuevosIds = $nuevosRecicladores
                ->map(fn($reciclador) => $reciclador->auth_user_id)
                ->filter()
                ->values()
                ->toArray();

            // Combinar y eliminar duplicados
            $todosIds = array_unique(array_merge($idsActuales, $nuevosIds));

            // Actualizar la solicitud
            $solicitud->ids_disponibles = json_encode($todosIds);
            $solicitud->save();

            Log::info('Nuevos recicladores encontrados y agregados', [
                'solicitud_id' => $this->solicitudId,
                'nuevos_recicladores' => count($nuevosIds),
                'total_recicladores' => count($todosIds),
                'intento' => $this->intentos + 1
            ]);

            // Cargar datos completos de la solicitud para las notificaciones
            $solicitud->load(['authUser', 'authUser.ciudadano', 'materiales']);

            // Notificar a los nuevos recicladores
            foreach ($nuevosRecicladores as $reciclador) {
                // Verificar si ya existe una notificación para este reciclador
                $notificacionExistente = DB::table('notificaciones_solicitudes')
                    ->where('solicitud_id', $solicitud->id)
                    ->where('reciclador_id', $reciclador->id)
                    ->exists();

                if (!$notificacionExistente) {
                    // Crear notificación en BD
                    DB::table('notificaciones_solicitudes')->insert([
                        'solicitud_id' => $solicitud->id,
                        'reciclador_id' => $reciclador->id,
                        'estado' => 'pendiente',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Emitir evento WebSocket
                    NuevaSolicitudInmediata::dispatch($solicitud, $reciclador->auth_user_id, 'inmediata');

                    Log::info('Notificación enviada a nuevo reciclador', [
                        'reciclador_id' => $reciclador->id,
                        'auth_user_id' => $reciclador->auth_user_id,
                        'solicitud_id' => $solicitud->id
                    ]);

                    // También enviar notificación push si es necesario
                    // $this->enviarNotificacionReciclador($reciclador, $solicitud);
                }
            }
        } else {
            Log::info('No se encontraron nuevos recicladores disponibles', [
                'solicitud_id' => $this->solicitudId,
                'intento' => $this->intentos + 1
            ]);

            // Si estamos en el último intento y no hay suficientes recicladores, aumentar el radio
            if ($this->intentos + 1 < $this->maxIntentos) {
                ActualizarRecicladoresdisponibles::dispatch(
                    $this->solicitudId,
                    $this->intentos + 1,
                    $this->maxIntentos,
                    $this->radioKm
                )->delay(now()->addSeconds(15)); // Programar cada 15 segundos

                Log::info('Programado próximo intento de búsqueda', [
                    'solicitud_id' => $this->solicitudId,
                    'próximo_intento' => $this->intentos + 2,
                    'max_intentos' => $this->maxIntentos,
                    'próxima_ejecución' => 'en 15 segundos'
                ]);
            }
        }

        // Si no hemos alcanzado el máximo de intentos, programar otro intento
        if ($this->intentos + 1 < $this->maxIntentos) {
            ActualizarRecicladoresdisponibles::dispatch(
                $this->solicitudId,
                $this->intentos,
                $this->intentos + 1,
                10 // Radio ampliado a 10km
            )->delay(now()->addSeconds(15)); // También 15 segundos

            Log::info('Programado próximo intento de búsqueda', [
                'solicitud_id' => $this->solicitudId,
                'próximo_intento' => $this->intentos + 2,
                'max_intentos' => $this->maxIntentos
            ]);
        } else {
            Log::info('Finalizada la búsqueda periódica de recicladores', [
                'solicitud_id' => $this->solicitudId,
                'total_intentos' => $this->intentos + 1
            ]);

            // Verificar si después de todos los intentos hay recicladores disponibles
            $idsFinal = json_decode($solicitud->ids_disponibles, true) ?: [];
            if (empty($idsFinal)) {
                $solicitud->estado = 'sin_recicladores';
                $solicitud->save();

                Log::info('No se encontraron recicladores después de todos los intentos', [
                    'solicitud_id' => $this->solicitudId
                ]);
            }
        }
    }

    /**
     * Encuentra nuevos recicladores que no estén ya en la lista de disponibles
     */
    protected function encontrarNuevosRecicladores($latitud, $longitud, $radioKm = 3, $limite = 10, $excludeIds = [])
    {
        Log::info('Buscando nuevos recicladores cercanos con Redis', [
            'latitud' => $latitud,
            'longitud' => $longitud,
            'radio_km' => $radioKm,
            'limite' => $limite,
            'exclude_ids' => count($excludeIds)
        ]);

        // Convertir radio de km a metros para Redis
        $radioMetros = $radioKm * 1000;

        // Buscar recicladores cercanos usando Redis GEORADIUS
        $recicladorIds = Redis::georadius(
            'recycler:locations:active',
            $longitud,
            $latitud,
            $radioMetros,
            'm',
            ['WITHDIST', 'ASC', 'COUNT' => $limite * 3] // Obtenemos más para filtrar después
        );

        if (empty($recicladorIds)) {
            Log::info("No se encontraron recicladores en Redis");
            return collect([]);
        }

        Log::info("Recicladores encontrados en radio: " . count($recicladorIds));

        // Procesar resultados de Redis
        $recicladoresFiltrados = collect();
        foreach ($recicladorIds as $item) {
            // Corregir la forma en que accedemos a los datos según la estructura real
            if (is_array($item) && isset($item[0]) && isset($item[1])) {
                $userId = $item[0];
                $distanciaMetros = (float)$item[1];
            } else if (is_string($item)) {
                $userId = $item;
                $distanciaMetros = 0;
            } else {
                Log::warning("Formato de respuesta de Redis desconocido", ['item' => $item]);
                continue;
            }

            // Ignorar IDs que ya están en la lista
            if (in_array($userId, $excludeIds)) {
                continue;
            }

            // Verificar si el reciclador está disponible
            $status = Redis::hget("recycler:status", $userId);

            // Solo incluir si está disponible
            if ($status === 'disponible') {
                // Obtener datos completos del reciclador desde Redis
                $locationData = Redis::get("recycler:location:{$userId}");

                if ($locationData) {
                    $locationData = json_decode($locationData, true);

                    // Obtener datos adicionales del reciclador desde DB
                    $reciclador = DB::table('recicladores as r')
                        ->join('auth_users as a', function ($join) use ($userId) {
                            $join->on('r.id', '=', 'a.profile_id')
                                ->where('a.id', '=', $userId);
                        })
                        ->select(
                            'r.id',
                            'r.name',
                            'r.telefono',
                            'r.logo_url',
                            'r.asociacion_id',
                            'a.id as auth_user_id',
                            'r.estado'
                        )
                        ->first();

                    // Verificar que la cuenta esté activa
                    if ($reciclador && (strcasecmp($reciclador->estado, 'Activo') === 0 ||
                        strcasecmp($reciclador->estado, 'activo') === 0)) {

                        // Añadir datos de ubicación y distancia
                        $reciclador->latitude = $locationData['latitude'] ?? 0;
                        $reciclador->longitude = $locationData['longitude'] ?? 0;
                        $reciclador->timestamp = $locationData['timestamp'] ?? now()->timestamp;
                        $reciclador->status = $status;
                        $reciclador->distancia = $distanciaMetros / 1000; // Convertir a km

                        $recicladoresFiltrados->push($reciclador);

                        Log::info("Nuevo reciclador encontrado", [
                            'id' => $reciclador->id,
                            'nombre' => $reciclador->name,
                            'distancia_km' => $reciclador->distancia
                        ]);

                        // Interrumpir si ya tenemos suficientes
                        if ($recicladoresFiltrados->count() >= $limite) {
                            break;
                        }
                    }
                }
            }
        }

        Log::info("Nuevos recicladores que cumplen todos los criterios: " . $recicladoresFiltrados->count());

        return $recicladoresFiltrados->values();
    }
}
