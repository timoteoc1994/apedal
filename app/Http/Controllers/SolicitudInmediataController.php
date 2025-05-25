<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SolicitudRecoleccion;
use App\Models\Reciclador;
use App\Models\Material;
use App\Models\Ubicacionreciladores;
use App\Models\AuthUser;
use Carbon\Carbon;
use App\Services\FirebaseService;
use App\Events\NuevaSolicitudInmediata;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ActualizarRecicladoresdisponibles;

//websocket


class SolicitudInmediataController extends Controller
{
    /**
     * Busca recicladores cercanos y crea una solicitud inmediata
     */
    public function buscarRecicladores(Request $request)
    {
        Log::info('Solicitud inmediata recibida entrando', $request->all());

        try {
            // Validar los datos de la solicitud
            $validatedData = $request->validate([
                'direccion' => 'required|string|max:255',
                'referencia' => 'required|string|max:255',
                'latitud' => 'required|numeric',
                'longitud' => 'required|numeric',
                'peso_total' => 'required|numeric|min:0.1',
                'materiales' => 'required|json',
                'imagenes.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'es_inmediata' => 'required', // Verificar que sea una solicitud inmediata
            ]);
            $validatedData['es_inmediata'] = filter_var($validatedData['es_inmediata'], FILTER_VALIDATE_BOOLEAN);


            // Obtener el usuario autenticado (ciudadano)
            $user = Auth::user();


            $rutasImagenes = [];
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $index => $imagen) {
                    $nombreImagen = time() . '_' . $index . '_' . Auth::id() . '.' . $imagen->getClientOriginalExtension();
                    $rutaImagen = $imagen->storeAs('solicitudes', $nombreImagen, 'public');
                    $rutasImagenes[] = $rutaImagen;
                }
            }


            Log::info('imagenes sacado');
            // Iniciar transacción
            //DB::beginTransaction();

            // Crear la solicitud utilizando el método create()
            $solicitud = SolicitudRecoleccion::create([
                'user_id' => Auth::id(),
                'asociacion_id' => null, // Se asignará después si un reciclador acepta
                'fecha' => Carbon::now()->format('Y-m-d'),
                'hora_inicio' => Carbon::now()->format('H:i'),
                'hora_fin' => Carbon::now()->addMinutes(30)->format('H:i'),
                'direccion' => $validatedData['direccion'],
                'referencia' => $validatedData['referencia'],
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'peso_total' => $validatedData['peso_total'],
                'imagen' => json_encode($rutasImagenes), // Guardar array de rutas en formato JSON
                'estado' => 'buscando_reciclador', // Estado especial para solicitudes inmediatas
                'ciudad' => $user->ciudadano->ciudad ?? 'No especificada',
                'es_inmediata' => $validatedData['es_inmediata'], // Establecer como solicitud inmediata
            ]);

            // Guardar materiales
            $materialesData = json_decode($validatedData['materiales'], true);
            foreach ($materialesData as $material) {
                Material::create([
                    'solicitud_id' => $solicitud->id,
                    'tipo' => $material['tipo'],
                    'peso' => $material['peso'],
                ]);
            }

            // Buscar recicladores cercanos
            $recicladores = $this->encontrarRecicladoresCercanos(
                $solicitud->latitud,
                $solicitud->longitud,
                3, // Radio en km
                10 // Máximo número de recicladores
            );

            if (empty($recicladores)) {
                // No hay recicladores disponibles
                $solicitud->estado = 'sin_recicladores';
                $solicitud->save();

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'No hay recicladores disponibles en este momento. Por favor, intenta agendar para mañana.',
                ], 404);
            }

            // Crear notificaciones para los recicladores encontrados
            //imprimir un log
            Log::info('Creando notificaciones para recicladores encontrados', [
                'recicladores' => $recicladores,
                'solicitud_id' => $solicitud->id
            ]);

            //actualizar el campo ids_disponibles de la solciitud con los ids de los recicladores disponibles esto debe ser un array y enviar un evento para actualizar los websockets
            //pero cada id tiene que poner la id del auth no id del recialdor
            $idsDisponibles = $recicladores
                ->map(fn($reciclador) => $reciclador->auth_user_id)
                ->filter()
                ->values()
                ->toArray();

            $solicitud->ids_disponibles = json_encode($idsDisponibles);
            $solicitud->save();

            Log::info('IDs de usuarios autenticados (auth_users) disponibles guardados', [
                'solicitud_id' => $solicitud->id,
                'ids_disponibles' => $idsDisponibles
            ]);


            // Cargar relaciones necesarias para el broadcast
            $solicitud->load(['authUser', 'authUser.ciudadano', 'materiales']);


            // Enviar notificación WebSocket a cada reciclador disponible
            foreach ($recicladores as $reciclador) {
                // Crear notificación en BD
                DB::table('notificaciones_solicitudes')->insert([
                    'solicitud_id' => $solicitud->id,
                    'reciclador_id' => $reciclador->id,
                    'estado' => 'pendiente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Emitir evento WebSocket pero el id reciclador atrer con el auth_user_id
                //event(new NuevaSolicitudInmediata($solicitud, $reciclador->auth_user_id));
                //log de $reciclador->auth_user_id
                Log::info('enviando al id' . $reciclador->auth_user_id);
                //event(new \App\Events\NuevaSolicitudInmediata($solicitud, $reciclador->auth_user_id));
                // Dispara el evento de broadcast
                NuevaSolicitudInmediata::dispatch($solicitud, $reciclador->auth_user_id, 'inmediata');

                // También enviar notificación push como respaldo
                // $this->enviarNotificacionReciclador($reciclador, $solicitud);

                Log::info('Notificación y evento emitidos para reciclador', [
                    'reciclador_id' => $reciclador->id,
                    'solicitud_id' => $solicitud->id
                ]);
            }



            // Iniciar el proceso de actualización periódica de recicladores disponibles
            ActualizarRecicladoresdisponibles::dispatch($solicitud->id, 0, 16, 3) // 16 intentos para un total de 4 minutos
                ->delay(now()->addSeconds(15));

            DB::commit();

            // Devolver información sobre la solicitud creada
            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada a recicladores cercanos. Espera su respuesta.',
                'data' => [
                    'solicitud_id' => $solicitud->id,
                    'recicladores_notificados' => count($recicladores),
                    'tiempo_espera' => 5, // minutos
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar solicitud inmediata: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitussssd: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encuentra recicladores cercanos usando la fórmula Haversine
     */
    private function encontrarRecicladoresCercanos($latitud, $longitud, $radioKm = 3, $limite = 10)
    {
        Log::info('Buscando recicladores cercanos con Redis', [
            'latitud' => $latitud,
            'longitud' => $longitud,
            'radio_km' => $radioKm,
            'limite' => $limite
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
            ['WITHDIST', 'ASC', 'COUNT' => $limite * 2] // Obtenemos más para filtrar después
        );

        if (empty($recicladorIds)) {
            Log::info("No se encontraron recicladores en Redis, intentando con un radio mayor");
            if ($radioKm < 10) {
                return $this->encontrarRecicladoresCercanos($latitud, $longitud, 10, $limite);
            }
            return collect([]);
        }

        Log::info("Recicladores encontrados en radio inicial: " . count($recicladorIds));

        // Inspeccionar la estructura real de los resultados para depuración
        Log::info("Estructura del primer resultado:", ['data' => json_encode($recicladorIds[0])]);

        // Procesar resultados de Redis
        $recicladoresFiltrados = collect();
        foreach ($recicladorIds as $item) {
            // Corregir la forma en que accedemos a los datos según la estructura real
            // La estructura puede ser diferente dependiendo de tu versión de Redis y phpredis

            // Caso 1: Si el resultado es un array plano [userId, distancia]
            if (is_array($item) && isset($item[0]) && isset($item[1])) {
                $userId = $item[0];
                $distanciaMetros = (float)$item[1];
            }
            // Caso 2: Si el resultado es un string (solo el ID)
            else if (is_string($item)) {
                $userId = $item;
                $distanciaMetros = 0; // No tenemos la distancia

                // Puedes calcular la distancia manualmente si es necesario
                // usando la fórmula Haversine
            }
            // Caso 3: Para otra estructura, necesitamos conocer el formato exacto
            else {
                Log::warning("Formato de respuesta de Redis desconocido", ['item' => $item]);
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
                        $reciclador->latitude = $locationData['latitude'];
                        $reciclador->longitude = $locationData['longitude'];
                        $reciclador->timestamp = $locationData['timestamp'];
                        $reciclador->status = $status;
                        $reciclador->distancia = $distanciaMetros / 1000; // Convertir a km

                        $recicladoresFiltrados->push($reciclador);

                        Log::info("Reciclador aceptado", [
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

        Log::info("Recicladores que cumplen todos los criterios: " . $recicladoresFiltrados->count());

        // Si no encontramos suficientes, intentar con radio mayor
        if ($recicladoresFiltrados->isEmpty() && $radioKm < 10) {
            Log::info("Intentando con un radio mayor: 10 km");
            return $this->encontrarRecicladoresCercanos($latitud, $longitud, 10, $limite);
        }

        return $recicladoresFiltrados->values();
    }

    /**
     * Envía notificación al reciclador (implementar según tu sistema de notificaciones)
     */
    private function enviarNotificacionReciclador($reciclador, $solicitud)
    {
        // Obtener el usuario Auth asociado al reciclador
        $authUser = AuthUser::where('profile_id', $reciclador->id)
            ->where('role', 'reciclador')
            ->first();

        if (!$authUser || empty($authUser->fcm_token)) {
            Log::warning('No se pudo enviar notificación al reciclador: no tiene token FCM', [
                'reciclador_id' => $reciclador->id
            ]);
            return;
        }

        // Implementar envío de notificación según tu sistema
        // Ejemplo usando el servicio Firebase:
        FirebaseService::sendNotification($authUser->id, [
            'title' => 'Nueva solicitud de recolección',
            'body' => 'Hay una recolección inmediata cerca de ti',
            'data' => [
                'solicitud_id' => $solicitud->id,
                'tipo' => 'solicitud_inmediata',
                'latitud' => $solicitud->latitud,
                'longitud' => $solicitud->longitud,
                'direccion' => $solicitud->direccion,
                'peso_total' => $solicitud->peso_total,
                'distancia' => round($reciclador->distancia, 2) . ' km'
            ]
        ]);

        Log::info('Notificación enviada al reciclador', [
            'reciclador_id' => $reciclador->id,
            'solicitud_id' => $solicitud->id
        ]);
    }

    /**
     * Verifica el estado actual de una solicitud inmediata
     */
    public function verificarEstado($id)
    {
        $solicitud = SolicitudRecoleccion::findOrFail($id);

        // Verificar que la solicitud pertenezca al usuario autenticado
        if ($solicitud->user_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver esta solicitud',
            ], 403);
        }

        Log::info('Verificando estado de la solicitud', [
            'solicitud_id' => $solicitud->id,
            'estado' => $solicitud->estado,
            'reciclador_id' => $solicitud->reciclador_id,
        ]);

        $respuesta = [
            'success' => true,
            'estado' => $solicitud->estado,
        ];

        // No necesitas nada más si no vas a mostrar datos del reciclador

        return response()->json($respuesta);
    }


    /**
     * Calcula la distancia entre dos puntos usando Haversine
     */
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distancia en kilómetros
    }
}
