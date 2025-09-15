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
    protected $ciudad; // 🆕 Agregar ciudad

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($solicitudId, $intentos = 0, $maxIntentos = 4, $radioKm = 3, $ciudad = null)
    {
        $this->solicitudId = $solicitudId;
        $this->intentos = $intentos;
        $this->maxIntentos = $maxIntentos;
        $this->radioKm = $radioKm;
        $this->ciudad = $ciudad; // 🆕 Asignar ciudad
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
{
    Log::info('Ejecutando búsqueda de recicladores', [
        'solicitud_id' => $this->solicitudId,
        'intento' => $this->intentos + 1,
        'max_intentos' => $this->maxIntentos,
        'radio_km' => $this->radioKm,
        'ciudad' => $this->ciudad // 🆕 Log de ciudad
    ]);

    $solicitud = SolicitudRecoleccion::find($this->solicitudId);

    // Verificar si la solicitud aún existe y sigue en estado de búsqueda
    if (!$solicitud || $solicitud->estado !== 'buscando_reciclador') {
        Log::info('Búsqueda de recicladores cancelada: solicitud no disponible o ya asignada', [
            'solicitud_id' => $this->solicitudId,
            'intento' => $this->intentos + 1,
            'estado_actual' => $solicitud->estado ?? 'no_encontrada'
        ]);
        return;
    }

    // Obtener los IDs actuales de recicladores disponibles
    $idsActuales = json_decode($solicitud->ids_disponibles, true) ?: [];

    // Calcular radio progresivo: empezar con 3km y aumentar gradualmente
    $radioActual = $this->radioKm + ($this->intentos * 1.5); // 3km, 4.5km, 6km, 7.5km...
    $radioActual = min($radioActual, 5); // Máximo 5km

    Log::info('Buscando recicladores con radio actual', [
        'solicitud_id' => $this->solicitudId,
        'radio_actual' => $radioActual,
        'intento' => $this->intentos + 1,
        'recicladores_actuales' => count($idsActuales),
        'ciudad_filtro' => $this->ciudad // 🆕 Log de ciudad
    ]);

    // Buscar nuevos recicladores cercanos usando Redis
    $nuevosRecicladores = $this->encontrarNuevosRecicladores(
        $solicitud->latitud,
        $solicitud->longitud,
        $radioActual,
        500,
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
            'intento' => $this->intentos + 1,
            'radio_usado' => $radioActual,
            'ciudad_filtro' => $this->ciudad // 🆕 Log de ciudad
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

                Log::info('Notificación enviada a reciclador', [
                    'reciclador_id' => $reciclador->id,
                    'auth_user_id' => $reciclador->auth_user_id,
                    'solicitud_id' => $solicitud->id,
                    'distancia_km' => $reciclador->distancia ?? 'N/A',
                    'ciudad_reciclador' => $reciclador->ciudad ?? 'N/A' // 🆕 Log de ciudad del reciclador
                ]);
            }
        }

        // Si es el primer intento y encontramos recicladores, notificar al usuario
        if ($this->intentos === 0 && count($nuevosIds) > 0) {
            Log::info('Primera búsqueda exitosa - recicladores encontrados', [
                'solicitud_id' => $this->solicitudId,
                'recicladores_encontrados' => count($nuevosIds)
            ]);
            
            // Aquí podrías enviar una notificación al usuario de que se encontraron recicladores
            // Por ejemplo, un evento WebSocket al usuario o notificación push
        }

    } else {
        Log::info('No se encontraron nuevos recicladores disponibles', [
            'solicitud_id' => $this->solicitudId,
            'intento' => $this->intentos + 1,
            'radio_usado' => $radioActual,
            'recicladores_actuales' => count($idsActuales),
            'ciudad_filtro' => $this->ciudad // 🆕 Log de ciudad
        ]);
    }

    // Continuar buscando si no hemos alcanzado el máximo de intentos
    if ($this->intentos + 1 < $this->maxIntentos) {
        // Programar próximo intento - 🆕 Pasar la ciudad al siguiente intento
        ActualizarRecicladoresdisponibles::dispatch(
            $this->solicitudId,
            $this->intentos + 1,
            $this->maxIntentos,
            $this->radioKm,
            $this->ciudad
        )->delay(now()->addSeconds(15)); // Cada 15 segundos

        Log::info('Programado próximo intento de búsqueda', [
            'solicitud_id' => $this->solicitudId,
            'próximo_intento' => $this->intentos + 2,
            'max_intentos' => $this->maxIntentos,
            'próxima_ejecución' => 'en 15 segundos'
        ]);
    } else {
        // Finalizar búsqueda
        Log::info('Finalizada la búsqueda periódica de recicladores', [
            'solicitud_id' => $this->solicitudId,
            'total_intentos' => $this->intentos + 1
        ]);

        // Verificar si después de todos los intentos hay recicladores disponibles
        $idsFinal = json_decode($solicitud->ids_disponibles, true) ?: [];
        if (empty($idsFinal)) {
            $solicitud->estado = 'sin_recicladores';
            $solicitud->save();

            Log::warning('No se encontraron recicladores después de todos los intentos', [
                'solicitud_id' => $this->solicitudId,
                'total_intentos' => $this->maxIntentos,
                'radio_maximo_usado' => $radioActual
            ]);
        } else {
            Log::info('Búsqueda finalizada con recicladores disponibles', [
                'solicitud_id' => $this->solicitudId,
                'total_recicladores' => count($idsFinal)
            ]);
        }
    }
}

    /**
     * Encuentra nuevos recicladores que no estén ya en la lista de disponibles
     * 🆕 Filtrados por ciudad
     */
    protected function encontrarNuevosRecicladores($latitud, $longitud, $radioKm = 3, $limite = 10, $excludeIds = [])
    {
        Log::info('Buscando nuevos recicladores cercanos con Redis y filtro de ciudad', [
            'latitud' => $latitud,
            'longitud' => $longitud,
            'radio_km' => $radioKm,
            'limite' => $limite,
            'exclude_ids' => count($excludeIds),
            'ciudad_filtro' => $this->ciudad // 🆕 Log de ciudad
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

                    // 🆕 AGREGAR LOG PARA VER QUÉ CONTIENE EXACTAMENTE LOCATIONDATA
                    Log::debug("Datos recuperados desde Redis para reciclador", [
                        'user_id' => $userId,
                        'location_data_completo' => $locationData,
                        'keys_disponibles' => array_keys($locationData ?? []),
                        'ciudad_value' => $locationData['ciudad'] ?? 'CAMPO_NO_EXISTE',
                        'ciudad_type' => gettype($locationData['ciudad'] ?? null)
                    ]);

                    // 🆕 FILTRO POR CIUDAD: Verificar que la ciudad coincida
                    $ciudadReciclador = $locationData['ciudad'] ?? null;
                    
                    // 🆕 Agregar más logs de comparación
                    Log::debug("Comparación de ciudades", [
                        'user_id' => $userId,
                        'ciudad_reciclador_original' => $ciudadReciclador,
                        'ciudad_requerida_original' => $this->ciudad,
                        'ciudad_reciclador_trimmed' => trim($ciudadReciclador ?? ''),
                        'ciudad_requerida_trimmed' => trim($this->ciudad ?? ''),
                        'comparacion_strcasecmp' => $ciudadReciclador ? strcasecmp(trim($ciudadReciclador), trim($this->ciudad)) : 'null'
                    ]);
                    
                    if (!$ciudadReciclador || 
                        strcasecmp(trim($ciudadReciclador), trim($this->ciudad)) !== 0) {
                        
                        Log::debug("Reciclador filtrado por ciudad diferente", [
                            'user_id' => $userId,
                            'ciudad_reciclador' => $ciudadReciclador,
                            'ciudad_requerida' => $this->ciudad,
                            'razon' => !$ciudadReciclador ? 'ciudad_null' : 'ciudad_diferente'
                        ]);
                        continue; // Saltar este reciclador
                    }

                    // 🔧 FIX: Obtener datos del reciclador desde Redis
                    $recicladorData = Redis::get("recycler:profile:{$userId}");
                    
                    if ($recicladorData) {
                        $recicladorData = json_decode($recicladorData, true);
                        
                        // Crear objeto similar al que devolvía la BD
                        $reciclador = (object) [
                            'id' => $recicladorData['id'] ?? null,
                            'name' => $recicladorData['name'] ?? 'Sin nombre',
                            'telefono' => $recicladorData['telefono'] ?? null,
                            'logo_url' => $recicladorData['logo_url'] ?? null,
                            'asociacion_id' => $recicladorData['asociacion_id'] ?? null,
                            'auth_user_id' => $userId,
                            'estado_redis' => $status
                        ];

                        Log::debug("Datos del reciclador desde Redis", [
                            'user_id' => $userId,
                            'reciclador_data_keys' => array_keys($recicladorData),
                            'status_redis' => $status
                        ]);
                    } else {
                        // Fallback: Si no está en Redis, buscar en BD y cachear
                        Log::info("Reciclador no encontrado en Redis, consultando BD", ['user_id' => $userId]);
                        
                        $recicladorDB = DB::table('recicladores as r')
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

                        if ($recicladorDB) {
                            // Cachear en Redis para próximas consultas (1 hora)
                            $profileData = [
                                'id' => $recicladorDB->id,
                                'name' => $recicladorDB->name,
                                'telefono' => $recicladorDB->telefono,
                                'logo_url' => $recicladorDB->logo_url,
                                'asociacion_id' => $recicladorDB->asociacion_id,
                                'estado_bd' => $recicladorDB->estado,
                                'cached_at' => now()->timestamp
                            ];
                            Redis::setex("recycler:profile:{$userId}", 3600, json_encode($profileData));
                            
                            $reciclador = $recicladorDB;
                            $reciclador->estado_redis = $status;
                            
                            Log::info("Reciclador cacheado en Redis desde BD", [
                                'user_id' => $userId,
                                'estado_bd' => $recicladorDB->estado
                            ]);
                        } else {
                            $reciclador = null;
                            Log::warning("Reciclador no encontrado ni en Redis ni en BD", ['user_id' => $userId]);
                        }
                    }

                    // 🔧 FIX: Verificar estado solo desde Redis (disponible)
                    if ($reciclador && ($status === 'disponible')) {

                        // Añadir datos de ubicación y distancia
                        $reciclador->latitude = $locationData['latitude'] ?? 0;
                        $reciclador->longitude = $locationData['longitude'] ?? 0;
                        $reciclador->timestamp = $locationData['timestamp'] ?? now()->timestamp;
                        $reciclador->status = $status;
                        $reciclador->distancia = $distanciaMetros / 1000; // Convertir a km
                        $reciclador->ciudad = $ciudadReciclador; // 🆕 Agregar ciudad

                        $recicladoresFiltrados->push($reciclador);

                        Log::info("Nuevo reciclador encontrado (estado desde Redis)", [
                            'id' => $reciclador->id,
                            'nombre' => $reciclador->name,
                            'estado_redis' => $status,
                            'distancia_km' => $reciclador->distancia,
                            'ciudad' => $ciudadReciclador,
                            'auth_user_id' => $userId
                        ]);

                        // Interrumpir si ya tenemos suficientes
                        if ($recicladoresFiltrados->count() >= $limite) {
                            break;
                        }
                    }
                }
            }
        }

        Log::info("Nuevos recicladores de la misma ciudad que cumplen todos los criterios", [
            'encontrados' => $recicladoresFiltrados->count(),
            'ciudad_filtro' => $this->ciudad // 🆕 Log de ciudad
        ]);

        return $recicladoresFiltrados->values();
    }
}