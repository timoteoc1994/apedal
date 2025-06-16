<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthUser;
use App\Models\Reciclador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Events\EliminacionSolicitud;



class MostrarSolicitudesController extends Controller
{
    public function getEstadisticas()
    {
        // Obtener la asociación autenticada
        $asociacion = Auth::user();

        // Verificar que el usuario es una asociación
        if (!$asociacion || $asociacion->role !== 'asociacion') {
            return response()->json([
                'message' => 'No autorizado',
            ], 403);
        }

        // Contar recicladores asociados a esta asociación
        $recicladoresCount = Reciclador::where('asociacion_id', $asociacion->id)->count();

        // Contar solicitudes pendientes para esta asociación
        $solicitudesCount = SolicitudRecoleccion::where('asociacion_id', $asociacion->id)
            ->where('estado', 'buscando_reciclador')
            ->count();

        // Devolver respuesta en formato JSON
        return response()->json([
            'recicladores_count' => $recicladoresCount,
            'solicitudes_count' => $solicitudesCount,
            'success' => true,
        ], 200);
    }
    // Modificación para la función enviarNotificacion
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

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data)
                ->withHighestPossiblePriority()
                ->toToken($token);

            $result = $messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notificación enviada',
                'result' => $result
            ];
        } catch (\Exception $e) {
            //\Log::error('Error al enviar notificación FCM: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
    public function index()
    {
        $solicitudes = SolicitudRecoleccion::where('asociacion_id', Auth::id())
            ->where('estado', 'buscando_reciclador')
            ->select('id', 'user_id', 'asociacion_id', 'hora_inicio', 'hora_fin', 'peso_total', 'estado', 'created_at')
            ->latest()
            ->get();

        foreach ($solicitudes as $solicitud) {
            $solicitud->user = AuthUser::with(['ciudadano' => function ($query) {
                // Si también quieres filtrar campos del ciudadano
                $query->select('id', 'name', 'logo_url', 'telefono');
            }])
                ->select('id', 'email', 'profile_id') // Selecciona solo los campos necesarios del usuario
                ->find($solicitud->user_id);
        }


        return response()->json([
            'success' => true,
            'data' => $solicitudes
        ]);
    }
    public function show($id)
    {
        $solicitud = SolicitudRecoleccion::where('id', $id)
            ->where('asociacion_id', Auth::id())
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
    public function crearReciclador(Request $request)
    {
        try {
            // Validar datos comunes
            $common = $request->validate([
                'email' => 'required|email|unique:auth_users,email',
                'password' => 'required|min:8|confirmed',
                'role' => 'required|in:ciudadano,reciclador,asociacion',
                'fcm_token' => 'nullable|string', // Añadir validación para token FCM
            ]);

            // Validar datos específicos según el rol
            $profileData = [];
            $profile = null;

            $profileData = $request->validate([
                'name' => 'required|string',
                'telefono' => 'required|string',
                'estado' => 'required|string',
            ]);

            //llamos a los datos de la asociacion para ponerle la misma ciudad y tambien su id
            $asociacion = AuthUser::with('asociacion')->find(Auth::id());
            $profileData['asociacion_id'] = $asociacion->asociacion->id;
            $profileData['ciudad'] = $asociacion->asociacion->city;
            $profileData['status'] = 'inactivo';


            $profile = Reciclador::create($profileData);


            // Crear usuario de autenticación con el token FCM
            $userData = [
                'email' => $common['email'],
                'password' => Hash::make($common['password']),
                'role' => $common['role'],
                'profile_id' => $profile->id,
            ];

            // Añadir el token FCM si está presente
            if ($request->has('fcm_token') && $request->fcm_token) {
                $userData['fcm_token'] = $request->fcm_token;
            }

            $user = AuthUser::create($userData);

            // Generar token si se usa Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso',
                'data' => [
                    'user' => $user,
                    'profile' => $profile,
                    'token' => $token
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() == 23505 || $e->getCode() == 1062) { // Códigos para clave duplicada en PostgreSQL y MySQL
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getReciclador(Request $request)
    {
        try {
            $user = AuthUser::with('reciclador.asociacion')
                ->where('role', 'reciclador')
                ->where('profile_id', $request->id)
                ->first();

            if (!$user || !$user->reciclador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reciclador no encontrado',
                ], 404);
            }

            // Formatear datos según lo esperado por la vista Flutter
            $recicladorData = [
                'name' => $user->reciclador->name,
                'estado' => $user->reciclador->estado, // Ajusta según tus estados
                'phone' => $user->reciclador->telefono,
                'email' => $user->email,
                'address' => $user->reciclador->ciudad, // O usar otro campo como dirección
                'logo_url' => $user->reciclador->logo_url,
                'total_recolecciones' => 0, // Debes obtener esto de otra tabla o calcularlo
                'total_kg' => 23, // Debes obtener esto de otra tabla o calcularlo
                'rating' => 0, // Debes obtener esto de otra tabla o calcularlo
            ];

            return response()->json([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'data' => $recicladorData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function asignarReciclador(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'solicitud_id' => 'required|integer|exists:solicitudes_recoleccion,id',
                'reciclador_id' => 'required|integer|exists:auth_users,id',
            ]);

            // Obtener el usuario autenticado (debe ser una asociación)
            $authUser = Auth::user();
            if ($authUser->role !== 'asociacion') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo las asociaciones pueden asignar recicladores',
                ], 403);
            }

            // Obtener la asociación
            $asociacion = $authUser->asociacion;
            if (!$asociacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la información de la asociación',
                ], 404);
            }


            // Obtener la solicitud
            $solicitud = SolicitudRecoleccion::findOrFail($request->solicitud_id);

            // Verificar si la solicitud ya está asignada o completada o cancelada
            if (in_array($solicitud->estado, ['asignado', 'en_camino', 'completado', 'cancelado', 'pendiente'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud ya está ' . $this->formatearEstado($solicitud->estado),
                ], 400);
            }

            // Obtener el usuario del reciclador
            $recicladorUser = AuthUser::where('id', $request->reciclador_id)
                ->where('role', 'reciclador')
                ->first();

            if (!$recicladorUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no es un reciclador',
                ], 400);
            }


            // Obtener el perfil del reciclador
            $reciclador = Reciclador::findOrFail($recicladorUser->profile_id);

            // Verificar que el reciclador pertenezca a la asociación actual
            if ($reciclador->asociacion_id != $asociacion->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este reciclador no pertenece a tu asociación',
                ], 400);
            }
            DB::beginTransaction();

            // Actualizar la solicitud
            $solicitud->reciclador_id = $recicladorUser->id;
            $solicitud->asociacion_id = $authUser->id; // ID del AuthUser de la asociación
            $solicitud->estado = 'asignado';
            $solicitud->save();


            DB::commit();

            // Notificar al reciclador (si tiene token de FCM)
            // Enviar notificación al reciclador que debe realizar la solcitud
            FirebaseService::sendNotification($recicladorUser->id, [
                'title' => 'Te agendo una nueva recoleccion',
                'body' => $asociacion->name . ' ha asignado una nueva recoleccion',
                'data' => [
                    'route' => '/',
                    'solicitud_id' => (string)$solicitud->id,
                ]
            ]);

            FirebaseService::sendNotification($solicitud->user_id, [
                'title' => 'Tu solicitud agendada fue aceptada',
                'body' => 'Puedes ver los detalles ahora',
                'data' => [
                    'route' => '/detalle_solicitud_ciudadano',
                    'solicitud_id' => (string)$solicitud->id,
                ] // Este campo es opcional pero recomendado
            ]);

            //Enviar el evento que la solicitud ya fue asignada y borrar en todas las pantallas
            // Disparar eventos para eliminar solicitudes
            $ids_disponibles = json_decode($solicitud->ids_disponibles);
            foreach ($ids_disponibles as $id_disponible) {
                EliminacionSolicitud::dispatch($solicitud, $id_disponible);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reciclador asignado con éxito',
                'data' => [
                    'solicitud' => $solicitud,
                    'reciclador' => [
                        'id' => $recicladorUser->id,
                        'name' => $reciclador->name,
                        'email' => $recicladorUser->email,
                        'phone' => $reciclador->telefono
                    ]
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Solicitud o reciclador no encontrado',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al asignar reciclador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener recicladores disponibles para asignar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerRecicladoresDisponibles()
    {
        try {
            // Obtener el usuario autenticado (debe ser una asociación)
            $authUser = Auth::user();

            // Obtener la asociación
            $asociacion = $authUser->asociacion;
            if (!$asociacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la información de la asociación',
                ], 404);
            }

            // Obtener recicladores que pertenecen a la asociación y están disponibles y activos
            $recicladores = Reciclador::where('asociacion_id', $asociacion->id)
                // ->where('status', 'disponible')
                ->where('estado', 'Activo')
                ->with(['authUser' => function ($query) {
                    $query->select('id', 'email', 'profile_id');
                }])
                ->get();

            // Formatear la respuesta
            $recicladoresDisponibles = [];

            foreach ($recicladores as $reciclador) {
                // Solo si tiene un usuario de autenticación asociado
                if ($reciclador->authUser) {
                    $recicladoresDisponibles[] = [
                        'id' => $reciclador->authUser->id, // ID del AuthUser
                        'name' => $reciclador->name,
                        'email' => $reciclador->authUser->email,
                        'phone' => $reciclador->telefono,
                        'status' => $reciclador->status,
                        'logo_url' => $reciclador->logo_url,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Recicladores disponibles obtenidos con éxito',
                'data' => $recicladoresDisponibles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recicladores disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Formatear el estado para mensajes
     *
     * @param string $estado
     * @return string
     */
    private function formatearEstado($estado)
    {
        $formatos = [
            'pendiente' => 'pendiente',
            'asignado' => 'asignada',
            'en_camino' => 'en camino',
            'completado' => 'completada',
            'cancelado' => 'cancelada',
        ];

        return $formatos[$estado] ?? $estado;
    }
}
