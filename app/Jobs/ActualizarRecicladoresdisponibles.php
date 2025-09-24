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

         // 🔍 DEBUG: Verificar qué claves existen en Redis
    $allKeys = Redis::keys('recycler:*');
    Log::info("Todas las claves de Redis que empiezan con 'recycler:'", [
        'claves_encontradas' => $allKeys,
        'total_claves' => count($allKeys)
    ]);
    
    // Verificar si existe la clave GEO
    $geoExists = Redis::exists('recycler:locations:active');
    Log::info("¿Existe la clave GEO 'recycler:locations:active'?", [
        'existe' => $geoExists ? 'SÍ' : 'NO'
    ]);

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
        foreach ($recicladorIds as $index => $item) {
            Log::info("🔍 PROCESANDO ITEM #{$index}", [
                'item_completo' => $item,
                'item_type' => gettype($item),
                'item_is_array' => is_array($item),
                'item_count' => is_array($item) ? count($item) : 'no_array'
            ]);

            // Corregir la forma en que accedemos a los datos según la estructura real
            if (is_array($item) && isset($item[0]) && isset($item[1])) {
                $userId = $item[0];
                $distanciaMetros = (float)$item[1];
            } else if (is_string($item)) {
                $userId = $item;
                $distanciaMetros = 0;
            } else {
                Log::warning("⚠️ Formato de respuesta de Redis desconocido", ['item' => $item]);
                continue;
            }

            Log::info("👤 PROCESANDO RECICLADOR", [
                'user_id' => $userId,
                'distancia_metros' => $distanciaMetros,
                'ya_excluido' => in_array($userId, $excludeIds)
            ]);

            // Ignorar IDs que ya están en la lista
            if (in_array($userId, $excludeIds)) {
                Log::info("⏭️ Reciclador ya incluido, saltando", ['user_id' => $userId]);
                continue;
            }

            // Verificar si el reciclador está disponible
            $status = Redis::hget("recycler:status", $userId);
            Log::info("🔍 VERIFICANDO ESTADO", [
                'user_id' => $userId,
                'status_redis' => $status,
                'status_type' => gettype($status),
                'status_es_disponible' => $status === 'disponible',
                'status_es_string' => is_string($status),
                'status_length' => is_string($status) ? strlen($status) : 'no_string'
            ]);

            // Solo incluir si está disponible
            if ($status === 'disponible') {
                Log::info("✅ RECICLADOR DISPONIBLE", ['user_id' => $userId]);
                // Obtener datos completos del reciclador desde Redis
                $locationData = Redis::get("recycler:location:{$userId}");
                Log::info("📍 DATOS DE UBICACIÓN", [
                    'user_id' => $userId,
                    'location_data_exists' => !empty($locationData),
                    'location_data_raw' => $locationData ? 'existe' : 'vacio'
                ]);

                if ($locationData) {
                    $locationData = json_decode($locationData, true);

                    Log::info("📍 LOCATION DATA DECODIFICADO", [
                        'user_id' => $userId,
                        'location_data_completo' => $locationData,
                        'keys_disponibles' => array_keys($locationData ?? []),
                        'ciudad_value' => $locationData['ciudad'] ?? 'CAMPO_NO_EXISTE',
                        'ciudad_type' => gettype($locationData['ciudad'] ?? null)
                    ]);

                    // 🆕 FILTRO POR CIUDAD: Verificar que la ciudad coincida
                    $ciudadReciclador = $locationData['ciudad'] ?? null;
                    
                    Log::info("🏙️ COMPARACIÓN DE CIUDADES", [
                        'user_id' => $userId,
                        'ciudad_reciclador_original' => $ciudadReciclador,
                        'ciudad_requerida_original' => $this->ciudad,
                        'ciudad_reciclador_trimmed' => trim($ciudadReciclador ?? ''),
                        'ciudad_requerida_trimmed' => trim($this->ciudad ?? ''),
                        'comparacion_strcasecmp_result' => $ciudadReciclador ? strcasecmp(trim($ciudadReciclador), trim($this->ciudad)) : 'null',
                        'ciudades_son_iguales' => $ciudadReciclador && strcasecmp(trim($ciudadReciclador), trim($this->ciudad)) === 0
                    ]);
                    
                    if (!$ciudadReciclador || 
                        strcasecmp(trim($ciudadReciclador), trim($this->ciudad)) !== 0) {
                        
                        Log::info("❌ RECICLADOR FILTRADO POR CIUDAD", [
                            'user_id' => $userId,
                            'ciudad_reciclador' => $ciudadReciclador,
                            'ciudad_requerida' => $this->ciudad,
                            'razon' => !$ciudadReciclador ? 'ciudad_null' : 'ciudad_diferente'
                        ]);
                        continue; // Saltar este reciclador
                    }

                    Log::info("✅ RECICLADOR PASA FILTRO DE CIUDAD", [
                        'user_id' => $userId,
                        'ciudad_coincide' => $ciudadReciclador
                    ]);

                    // 🔧 FIX: Obtener datos del reciclador desde Redis
                    $recicladorData = Redis::get("recycler:profile:{$userId}");
                    Log::info("👤 PERFIL DEL RECICLADOR", [
                        'user_id' => $userId,
                        'profile_data_exists' => !empty($recicladorData),
                        'profile_data_raw' => $recicladorData ? 'existe' : 'no_existe'
                    ]);
                    
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

                        Log::info("✅ PERFIL OBTENIDO DESDE REDIS", [
                            'user_id' => $userId,
                            'reciclador_id' => $reciclador->id,
                            'reciclador_name' => $reciclador->name,
                            'status_redis' => $status
                        ]);
                    } else {
                        // Fallback: Si no está en Redis, buscar en BD y cachear
                        Log::info("🗄️ BUSCANDO EN BD", ['user_id' => $userId]);
                        
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

                        Log::info("🗄️ CONSULTA BD COMPLETADA", [
                            'user_id' => $userId,
                            'encontrado_en_bd' => !empty($recicladorDB),
                            'reciclador_db_id' => $recicladorDB->id ?? 'no_encontrado'
                        ]);

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
                            
                            Log::info("💾 RECICLADOR CACHEADO DESDE BD", [
                                'user_id' => $userId,
                                'estado_bd' => $recicladorDB->estado
                            ]);
                        } else {
                            $reciclador = null;
                            Log::warning("❌ RECICLADOR NO ENCONTRADO NI EN REDIS NI EN BD", ['user_id' => $userId]);
                        }
                    }

                    // 🔧 FIX: Verificar estado y agregar a la colección
                    if ($reciclador && ($status === 'disponible')) {
                        // Añadir datos de ubicación y distancia
                        $reciclador->latitude = $locationData['latitude'] ?? 0;
                        $reciclador->longitude = $locationData['longitude'] ?? 0;
                        $reciclador->timestamp = $locationData['timestamp'] ?? now()->timestamp;
                        $reciclador->status = $status;
                        $reciclador->distancia = $distanciaMetros / 1000; // Convertir a km
                        $reciclador->ciudad = $ciudadReciclador; // 🆕 Agregar ciudad

                        $recicladoresFiltrados->push($reciclador);

                        Log::info("🎉 RECICLADOR AGREGADO A LA LISTA", [
                            'id' => $reciclador->id,
                            'nombre' => $reciclador->name,
                            'estado_redis' => $status,
                            'distancia_km' => $reciclador->distancia,
                            'ciudad' => $ciudadReciclador,
                            'auth_user_id' => $userId,
                            'total_encontrados_hasta_ahora' => $recicladoresFiltrados->count()
                        ]);

                        // Interrumpir si ya tenemos suficientes
                        if ($recicladoresFiltrados->count() >= $limite) {
                            Log::info("🚀 LÍMITE ALCANZADO", [
                                'limite' => $limite,
                                'encontrados' => $recicladoresFiltrados->count()
                            ]);
                            break;
                        }
                    } else {
                        Log::warning("❌ RECICLADOR NO CUMPLE CRITERIOS FINALES", [
                            'user_id' => $userId,
                            'reciclador_existe' => !empty($reciclador),
                            'status' => $status,
                            'status_es_disponible' => $status === 'disponible'
                        ]);
                    }
                } else {
                    Log::warning("❌ NO HAY DATOS DE UBICACIÓN", ['user_id' => $userId]);
                }
            } else {
                Log::info("❌ RECICLADOR NO DISPONIBLE", [
                    'user_id' => $userId,
                    'status_actual' => $status,
                    'requerido' => 'disponible'
                ]);
            }
        }

        Log::info("🏁 RESULTADO FINAL DEL PROCESAMIENTO", [
            'recicladores_procesados_total' => count($recicladorIds),
            'recicladores_encontrados_finales' => $recicladoresFiltrados->count(),
            'ciudad_filtro' => $this->ciudad,
            'recicladores_finales' => $recicladoresFiltrados->map(function($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'auth_user_id' => $r->auth_user_id,
                    'distancia_km' => $r->distancia,
                    'ciudad' => $r->ciudad
                ];
            })->toArray()
        ]);

        return $recicladoresFiltrados->values();
    }
}