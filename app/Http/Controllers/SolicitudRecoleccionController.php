<?php

namespace App\Http\Controllers;

use App\Events\NuevaSolicitudInmediata;
use App\Events\SolicitudAgendada;
use Illuminate\Http\Request;
use App\Models\SolicitudRecoleccion;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\NotificationService;
use App\Models\AuthUser;
use App\Models\Asociacion;
use App\Models\Zona;
use Illuminate\Support\Facades\Log;
use App\Events\EliminacionSolicitud;

//los demas
use Illuminate\Support\Facades\DB;
use App\Models\Reciclador;
use App\Models\Ubicacionreciladores;
use Illuminate\Support\Facades\Redis;

class SolicitudRecoleccionController extends Controller
{
    /*  protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    } */
    public function obtenerDetallesRecoleccion($id)
    {
        Log::info('ID de solicitud recibido: ' . $id);
        try {
            $solicitud = SolicitudRecoleccion::find($id);
            Log::info('Solicitud encontrada: ' . $solicitud->reciclador);
            // Verificar que la solicitud pertenezca al usuario autenticado
            if ($solicitud->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver esta solicitud',
                ], 403);
            }

            $respuesta = [
                'success' => true,
                'estado' => $solicitud->estado,
                'solicitud' => [
                    'id' => $solicitud->id,
                    'direccion' => $solicitud->direccion,
                    'referencia' => $solicitud->referencia,
                    'latitud' => $solicitud->latitud,
                    'longitud' => $solicitud->longitud,
                ],
            ];

            // Si hay un reciclador asignado, añadir su información
            // Si hay un reciclador asignado, añadir su información
            $authUser = AuthUser::find($solicitud->reciclador_id);
            $datosReciclador = Reciclador::find($authUser->profile_id);

            // Imprimir para depuración
            Log::info('Datos del reciclador: ' . json_encode($datosReciclador));
            Log::info('Nombre del reciclador: ' . $datosReciclador->name);

            if ($datosReciclador) {
                Log::info('SI LLEGAMOS HASTA AQUI');
                $respuesta['recolector'] = [
                    'id' => $datosReciclador->id,
                    'nombre' => $datosReciclador->name,
                    'telefono' => $datosReciclador->telefono,
                    'foto' => $datosReciclador->logo_url,
                ];
                Log::info('SI LLEGAMOS HASTA AQUI X2');
                //HACER AUTH
                // Usar el ID correcto del AuthUser para Redis
                $authUserId = $authUser->id; // Este es el ID del AuthUser, no el profile_id
                // Intentar obtener ubicación desde Redis primero
                $redisKey = "recycler:location:{$authUserId}";
                Log::info('Buscando en Redis con clave: ' . $redisKey);

                $locationData = Redis::get($redisKey);
                Log::info('SI LLEGAMOS HASTA AQUI X3');
                Log::info('LocationData: ' . ($locationData ?: 'No encontrado'));

                if ($locationData) {
                    Log::info('SI LLEGAMOS HASTA AQUI X4');
                    $locationData = json_decode($locationData, true);
                    $respuesta['recolector']['ubicacion'] = [
                        'latitud' => $locationData['latitude'],
                        'longitud' => $locationData['longitude'],
                        'actualizado' => $locationData['timestamp'],
                    ];
                } else {
                    // Si no está en Redis, buscar en la base de datos
                    Log::info('Buscando en DB con auth_user_id: ' . $authUserId);

                    $ubicacion = Ubicacionreciladores::where('auth_user_id', $authUserId)
                        ->orderBy('timestamp', 'desc')
                        ->first();

                    if ($ubicacion) {
                        Log::info('Encontrada ubicación en DB: ' . json_encode($ubicacion));
                        $respuesta['recolector']['ubicacion'] = [
                            'latitud' => $ubicacion->latitude,
                            'longitud' => $ubicacion->longitude,
                            'actualizado' => $ubicacion->timestamp,
                        ];
                    } else {
                        Log::info('No se encontró ubicación en la base de datos');
                    }
                }
            }

            return response()->json($respuesta);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de recolección: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la recolección',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar estado de una solicitud
     * Mantiene compatibilidad con el código existente
     */

    private function enviarNotificacion2($token, $title, $body, $data = [])
    {
        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(storage_path('app/appedal-ffe02-firebase-adminsdk-fbsvc-98fe6577e7.json'));

            $messaging = $factory->createMessaging();

            $notification = \Kreait\Firebase\Messaging\Notification::create(
                $title,
                $body
            );

            // Asegurar que la data incluya todos los campos necesarios
            $data = array_merge([
                'timestamp' => time(),
                'tipo' => 'general', // Tipo por defecto
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Necesario para Flutter
            ], $data);

            // Usar CloudMessage::new() y configurar con la más alta prioridad
            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withHighestPossiblePriority() // Establece la más alta prioridad
                ->toToken($token);

            $result = $messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notificación enviada',
                'result' => $result
            ];
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación FCMc: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
    public function index(Request $request)
    {
        // Inicializa la consulta base con el usuario autenticado
        $query = SolicitudRecoleccion::where('user_id', Auth::id());

        // Determinar el rango de fechas a usar
        if ($request->has('fecha_inicio') || $request->has('fecha_fin')) {
            // Si se proporcionó al menos una fecha, usamos esos parámetros
            if ($request->has('fecha_inicio')) {
                $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
                $query->whereDate('created_at', '>=', $fechaInicio);
            }

            if ($request->has('fecha_fin')) {
                $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
                $query->whereDate('created_at', '<=', $fechaFin);
            }
        } else {
            // Si no se proporcionó ninguna fecha, usamos el día actual
            $hoy = Carbon::now()->startOfDay();
            $finHoy = Carbon::now()->endOfDay();
            $query->whereBetween('created_at', [$hoy, $finHoy]);

            // Si no hay resultados para hoy, ampliar al mes actual
            $conteoHoy = clone $query;
            if ($conteoHoy->count() == 0) {
                $query = SolicitudRecoleccion::where('user_id', Auth::id());
                $inicioMes = Carbon::now()->startOfMonth();
                $finMes = Carbon::now()->endOfMonth();
                $query->whereBetween('created_at', [$inicioMes, $finMes]);
            }
        }

        // Ejecutar la consulta final
        $solicitudes = $query->with('materiales')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $solicitudes
        ]);
    }

    /**
     * Almacenar una nueva solicitud de recolección
     */
    //meotodos para el puntoEnpoligono
    // Función para verificar si un punto está dentro de un polígono usando el algoritmo de Ray Casting
    /**
     * Determinar si un punto está dentro de un polígono usando el algoritmo de ray casting
     * 
     * @param array $punto ['lat' => float, 'lng' => float]
     * @param array $poligono Array de puntos ['lat' => float, 'lng' => float]
     * @return bool
     */
    private function puntoEnPoligono($punto, $poligono)
    {
        if (empty($poligono)) {
            return false;
        }

        $dentro = false;
        $j = count($poligono) - 1;

        for ($i = 0; $i < count($poligono); $i++) {
            if ((($poligono[$i]['lat'] < $punto['lat'] && $poligono[$j]['lat'] >= $punto['lat']) ||
                    ($poligono[$j]['lat'] < $punto['lat'] && $poligono[$i]['lat'] >= $punto['lat'])) &&
                ($poligono[$i]['lng'] + ($punto['lat'] - $poligono[$i]['lat']) /
                    ($poligono[$j]['lat'] - $poligono[$i]['lat']) *
                    ($poligono[$j]['lng'] - $poligono[$i]['lng']) < $punto['lng'])
            ) {
                $dentro = !$dentro;
            }
            $j = $i;
        }

        return $dentro;
    }

    /**
     * Calcular el centroide de un polígono
     * 
     * @param array $poligono Array de puntos ['lat' => float, 'lng' => float]
     * @return array ['lat' => float, 'lng' => float]
     */
    private function calcularCentroide($poligono)
    {
        if (empty($poligono)) {
            return ['lat' => 0, 'lng' => 0];
        }

        $n = count($poligono);
        $sumLat = 0;
        $sumLng = 0;

        foreach ($poligono as $punto) {
            $sumLat += $punto['lat'];
            $sumLng += $punto['lng'];
        }

        return [
            'lat' => $sumLat / $n,
            'lng' => $sumLng / $n
        ];
    }

    /**
     * Calcular la distancia entre dos puntos usando la fórmula de Haversine
     * 
     * @param array $punto1 ['lat' => float, 'lng' => float]
     * @param array $punto2 ['lat' => float, 'lng' => float]
     * @return float Distancia en kilómetros
     */
    private function calcularDistancia($punto1, $punto2)
    {
        // Radio de la Tierra en kilómetros
        $radioTierra = 6371;

        // Convertir latitudes y longitudes de grados a radianes
        $lat1 = deg2rad($punto1['lat']);
        $lng1 = deg2rad($punto1['lng']);
        $lat2 = deg2rad($punto2['lat']);
        $lng2 = deg2rad($punto2['lng']);

        // Diferencias de latitud y longitud
        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;

        // Fórmula de Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Distancia en kilómetros
        $distancia = $radioTierra * $c;

        return $distancia;
    }
    /**
     * Obtener los IDs de todos los recicladores de una asociación
     * 
     * @param Asociacion $asociacion
     * @return array IDs de los recicladores
     */
    private function obtenerRecicladoresdisponibles($asociacion)
    {
        $recicladores = $asociacion->recicladores()
            ->with('authUser')
            ->get();

        $ids = [];

        foreach ($recicladores as $reciclador) {
            if ($reciclador->authUser) {
                $ids[] = $reciclador->authUser->id;
            }
        }

        return $ids;
    }
    public function store(Request $request)
    {
        //imprimir request
        Log::info('Request: ' . $request->estado);

        // Validar los datos básicos
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'direccion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'peso_total' => 'required|numeric|min:0.1',
            'materiales' => 'required|json',
            'imagenes.*' => 'nullable|image|max:5120', // Cambiado a imagenes.* para coincidir con Flutter
            'es_inmediata' => 'required',
        ]);

        //anadir una variable a varidator
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Decodificar los materiales
        $materiales = json_decode($request->materiales, true);

        // Validar la estructura de los materiales
        if (!is_array($materiales) || empty($materiales)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar al menos un tipo de material',
            ], 422);
        }

        // Procesar las imágenes si existen
        $rutasImagenes = [];
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $index => $imagen) {
                $nombreImagen = time() . '_' . $index . '_' . Auth::id() . '.' . $imagen->getClientOriginalExtension();
                $rutaImagen = $imagen->storeAs('solicitudes', $nombreImagen, 'public');
                $rutasImagenes[] = $rutaImagen;
            }
        }

        //------esto para verificar si la solicitud cae dentro de una zona de una asociacion------
        //ver a que asociacion va permanecer la solicitud y si no cae dentro de una zona se puede abrir para que cualquier zona pueda recogerla
        //mis coordenadas de recoleccion
        $latitud = $request->latitud;
        $longitud = $request->longitud;
        //1.primero el profile_id del usuario que creo la solicitud
        // Crear punto con las coordenadas de la solicitud
        $punto = [
            'lat' => (float)$latitud,
            'lng' => (float)$longitud
        ];
        // Obtener el usuario y su ciudad
        $user = AuthUser::with('ciudadano')->find(Auth::id());

        if (!$user || !$user->ciudadano) {
            \Log::warning('No se encontró un usuario autenticado o no tiene un ciudadano asociado.');
            return response()->json(['error' => 'Usuario no válido'], 400);
        }

        \Log::info('El usuario registrado es de la ciudad: ' . $user->ciudadano->ciudad);

        // Buscar todas las asociaciones 
        // Después de obtener las asociaciones
        $asociaciones = Asociacion::with(['zonas', 'recicladores'])->where('verified', 1)->get();

        // Variables para guardar resultados
        $asociacion_id = null;
        $zona_id = null;
        $zonaEncontrada = null;
        $distanciaMinima = PHP_FLOAT_MAX;
        $ids_disponibles = [];

        // Crear punto con las coordenadas de la solicitud
        $punto = [
            'lat' => (float)$latitud,
            'lng' => (float)$longitud
        ];

        // Recorrer todas las asociaciones y sus zonas
        foreach ($asociaciones as $asociacion) {
            foreach ($asociacion->zonas as $zona) {
                $coordenadas = $zona->coordenadas;

                // Verificar si el punto está dentro de la zona
                if ($this->puntoEnPoligono($punto, $coordenadas)) {
                    $asociacion_id = $asociacion->id;
                    $zona_id = $zona->id;
                    $zonaEncontrada = $zona;

                    // Obtener los IDs de todos los recicladores de esta asociación
                    $ids_disponibles = $this->obtenerRecicladoresdisponibles($asociacion);

                    \Log::info("Punto dentro de la zona: {$zona->nombre} de la asociación: {$asociacion->nombre}");
                    break 2; // Salir de ambos bucles si encontramos la zona
                }

                // Si no está dentro, calcular la distancia al centroide de la zona
                $centroide = $this->calcularCentroide($coordenadas);
                $distancia = $this->calcularDistancia($punto, $centroide);

                // Actualizar si encontramos una zona más cercana
                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $asociacion_id = $asociacion->id;
                    $zona_id = $zona->id;
                    $zonaEncontrada = $zona;

                    // Obtener los IDs de todos los recicladores de esta asociación
                    $ids_disponibles = $this->obtenerRecicladoresdisponibles($asociacion);
                }
            }
        }



        //------fin buscar asociacion------
        //actualizar el asociacion_id para buscar el el perfil en auth
        //imprimiendo el asociacion_id
        \Log::info('El asociacion_id es: ' . $asociacion_id);
        $usuario = AuthUser::where('profile_id', $asociacion_id)->where('role', 'asociacion')->first();
        //imprimor usuario;
        \Log::info('El usuario es: ' . $usuario);
        // Crear la solicitud
        $solicitud = SolicitudRecoleccion::create([
            'user_id' => Auth::id(),
            'asociacion_id' => $usuario->id,
            'zona_id' => $zona_id,
            'ids_disponibles' => json_encode($ids_disponibles),
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'direccion' => $request->direccion,
            'referencia' => $request->referencia,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'peso_total' => $request->peso_total,
            'imagen' => json_encode($rutasImagenes), // Guardar array de rutas en formato JSON
            'estado' => 'buscando_reciclador',
            'ciudad' => $user->ciudadano->ciudad,
        ]);

        // Guardar los materiales
        foreach ($materiales as $material) {
            $solicitud->materiales()->create([
                'tipo' => $material['tipo'],
                'peso' => $material['peso'],
            ]);
        }

        // Cargar relación de materiales
        $solicitud->load('materiales');

        //enviar evento a los recicladores
        // Enviar evento a los recicladores
        foreach ($ids_disponibles as $recicladorId) {
            SolicitudAgendada::dispatch($solicitud, $recicladorId, 'agendada');
        }



        /* // Enviar notificación por firebase al usuario que creó la solicitud
        $user = AuthUser::find(Auth::id());
        if ($user && $user->fcm_token) {
            $this->enviarNotificacion(
                $user->fcm_token,
                'Solicitud creada con éxito',
                'Tu solicitud de recolección ha sido registrada y está pendiente de asignación.',
                [
                    'solicitud_id' => $solicitud->id,
                    'tipo' => 'solicitud_creada',
                    'fecha' => $solicitud->fecha
                ]
            );
        } */

        // Enviar notificación a la asociacion quien debe retirar
        // Enviar notificación a la asociación que debe atender la solicitud
        /* if ($asociacion_id) {
            // Buscar el usuario de esta asociación que tenga token FCM
            $usuarioAsociacion = AuthUser::find($asociacion_id);

            if ($usuarioAsociacion && $usuarioAsociacion->fcm_token) {
                $this->enviarNotificacion2(
                    $usuarioAsociacion->fcm_token,
                    'Nueva solicitud de recolección',
                    'Se ha registrado una nueva solicitud en tu zona.',
                    [
                        'solicitud_id' => (string)$solicitud->id,
                        'tipo' => 'nueva_solicitud',
                        'asociacion_id' => (string)$asociacion_id,
                        'zona_id' => $zonaEncontrada ? (string)$zonaEncontrada->id : '',
                        'direccion' => $solicitud->direccion,
                        'peso_total' => (string)$solicitud->peso_total
                    ]
                );
            }
        } */

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente',
            'data' => $solicitud
        ], 201);
    }

    /**
     * Mostrar los detalles de una solicitud específica
     */
    public function show($id)
    {
        $solicitud = SolicitudRecoleccion::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['materiales', 'reciclador'])
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $solicitud
        ]);
    }

    /**
     * Cancelar una solicitud de recolección (solo si está pendiente)
     */
    // Reemplaza el método destroy actual con este:
    public function destroy($id)
    {
        $solicitud = SolicitudRecoleccion::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if ($solicitud->estado !== 'buscando_reciclador') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar solicitudes pendientes'
            ], 422);
        }

        // Actualizar estado a 'cancelado'
        $solicitud->update(['estado' => 'cancelado']);

        //disparar evento de eliminar de la lista
        //disparar el evento NuevaSolicitudInmediata para todos los ids_disponibles que es json
        $ids_disponibles = json_decode($solicitud->ids_disponibles);
        foreach ($ids_disponibles as $id_disponible) {
            log::info('Disparando el evento para eliminar la solicitud de los otros usuarios');
            EliminacionSolicitud::dispatch($solicitud, $id_disponible);
        }


        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada correctamente'
        ]);
    }

    //recilador
    public function actualizarUbicacion(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $user = Auth::user();

            // Verificar que el usuario es un reciclador
            if ($user->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden actualizar su ubicación',
                ], 403);
            }

            // Guardar la ubicación
            Ubicacionreciladores::create([
                'auth_user_id' => $user->id,
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'timestamp' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar ubicación del reciclador: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la ubicación: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permite a un reciclador aceptar una solicitud inmediata
     */
    public function aceptarSolicitud($id, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario es un reciclador
            if ($user->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden aceptar solicitudes',
                ], 403);
            }

            // Obtener el perfil del reciclador
            $reciclador = Reciclador::where('id', $user->profile_id)->first();

            if (!$reciclador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de reciclador no encontrado',
                ], 404);
            }

            // Verificar estado del reciclador
            if ($reciclador->status !== 'activo' || $reciclador->estado !== 'disponible') {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes aceptar solicitudes en tu estado actual',
                ], 400);
            }

            DB::beginTransaction();

            // Buscar la solicitud
            $solicitud = SolicitudRecoleccion::findOrFail($id);

            // Verificar que la solicitud esté en estado "buscando_reciclador"
            if ($solicitud->estado !== 'buscando_reciclador') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya no está disponible',
                ], 400);
            }

            // Verificar que este reciclador haya sido notificado para esta solicitud
            $notificacion = DB::table('notificaciones_solicitudes')
                ->where('solicitud_id', $id)
                ->where('reciclador_id', $reciclador->id)
                ->where('estado', 'pendiente')
                ->first();

            if (!$notificacion) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No estás autorizado para aceptar esta solicitud',
                ], 403);
            }

            // Actualizar la notificación
            DB::table('notificaciones_solicitudes')
                ->where('id', $notificacion->id)
                ->update([
                    'estado' => 'aceptada',
                    'updated_at' => now(),
                ]);

            // Asignar el reciclador a la solicitud
            $solicitud->reciclador_id = $reciclador->id;
            $solicitud->estado = 'asignado';
            $solicitud->save();

            // Actualizar estado del reciclador
            $reciclador->estado = 'ocupado';
            $reciclador->save();

            // Marcar las demás notificaciones como expiradas
            DB::table('notificaciones_solicitudes')
                ->where('solicitud_id', $id)
                ->where('reciclador_id', '!=', $reciclador->id)
                ->update([
                    'estado' => 'expirada',
                    'updated_at' => now(),
                ]);

            DB::commit();

            // Enviar notificación al ciudadano
            $this->notificarCiudadano($solicitud, $reciclador);

            // Obtener información detallada de la solicitud para el reciclador
            $detallesSolicitud = $this->obtenerDetallesSolicitud($solicitud);

            return response()->json([
                'success' => true,
                'message' => 'Has aceptado la solicitud correctamente',
                'data' => $detallesSolicitud,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aceptar solicitud: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'solicitud_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permite a un reciclador rechazar una solicitud inmediata
     */
    public function rechazarSolicitud($id, Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario es un reciclador
            if ($user->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden rechazar solicitudes',
                ], 403);
            }

            // Obtener el perfil del reciclador
            $reciclador = Reciclador::where('id', $user->profile_id)->first();

            if (!$reciclador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de reciclador no encontrado',
                ], 404);
            }

            // Buscar la notificación correspondiente
            $notificacion = DB::table('notificaciones_solicitudes')
                ->where('solicitud_id', $id)
                ->where('reciclador_id', $reciclador->id)
                ->where('estado', 'pendiente')
                ->first();

            if (!$notificacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes una notificación pendiente para esta solicitud',
                ], 404);
            }

            // Actualizar la notificación como rechazada
            DB::table('notificaciones_solicitudes')
                ->where('id', $notificacion->id)
                ->update([
                    'estado' => 'rechazada',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Has rechazado la solicitud correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'solicitud_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notifica al ciudadano que un reciclador ha aceptado su solicitud
     */
    private function notificarCiudadano($solicitud, $reciclador)
    {
        // Implementar según el sistema de notificaciones que uses
        // Por ejemplo, usando Firebase Cloud Messaging

        Log::info('Envío de notificación al ciudadano', [
            'solicitud_id' => $solicitud->id,
            'reciclador_id' => $reciclador->id,
        ]);

        // Aquí implementarías el envío real de la notificación
        // Por ejemplo:
        // FirebaseService::sendNotification($solicitud->user_id, [
        //     'title' => 'Tu solicitud ha sido aceptada',
        //     'body' => 'Un reciclador aceptó tu solicitud y está en camino',
        //     'data' => [
        //         'solicitud_id' => $solicitud->id,
        //         'reciclador_id' => $reciclador->id,
        //     ]
        // ]);
    }

    /**
     * Obtiene los detalles de una solicitud para mostrar al reciclador
     */
    private function obtenerDetallesSolicitud($solicitud)
    {
        // Cargamos las relaciones
        $solicitud->load('materiales', 'authUser');

        // Obtenemos el ciudadano asociado al usuario
        $ciudadano = $solicitud->authUser->ciudadano;

        return [
            'id' => $solicitud->id,
            'fecha' => $solicitud->fecha,
            'hora_inicio' => $solicitud->hora_inicio,
            'hora_fin' => $solicitud->hora_fin,
            'direccion' => $solicitud->direccion,
            'referencia' => $solicitud->referencia,
            'latitud' => $solicitud->latitud,
            'longitud' => $solicitud->longitud,
            'peso_total' => $solicitud->peso_total,
            'estado' => $solicitud->estado,
            'imagen' => asset('storage/' . $solicitud->imagen),
            'imagenes_adicionales' => $solicitud->imagenes_adicionales ?
                array_map(function ($img) {
                    return asset('storage/' . $img);
                }, json_decode($solicitud->imagenes_adicionales, true)) : [],
            'materiales' => $solicitud->materiales->map(function ($material) {
                return [
                    'tipo' => $material->tipo,
                    'peso' => $material->peso,
                ];
            }),
            'ciudadano' => [
                'nombre' => $ciudadano->name ?? 'Usuario',
                'telefono' => $ciudadano->telefono ?? 'No disponible',
                'foto' => $ciudadano->logo_url ?? null,
            ],
        ];
    }
}
