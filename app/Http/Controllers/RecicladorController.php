<?php

namespace App\Http\Controllers;

use App\Events\ActualizarPuntosCiudadano;
use App\Events\CancelarSolicitudReciclador;
use App\Models\Reciclador;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\Log;
use App\Models\Ciudadano;
use App\Models\City;             // ← importa el modelo City
use App\Models\Asociacion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Events\NuevaSolicitudInmediata;
use App\Events\EliminacionSolicitud;
use Illuminate\Support\Facades\DB;
use App\Models\Material;
//importar validator
use Illuminate\Support\Facades\Validator;
use App\Models\AuthUser;
use App\Services\FirebaseService;
use Carbon\Carbon;
use App\Events\SolicitudCompleto;

class RecicladorController extends Controller
{
    //obtener detalles de una solcitud
    public function show($id)
    {
        $solicitud = SolicitudRecoleccion::where('id', $id)
            ->where('reciclador_id', Auth::id())
            ->with(['materiales', 'authUser.ciudadano'])
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


    public function getAsociaciones()
    {
        $asociaciones = Asociacion::all(); // Obtener todas las asociaciones
        return response()->json([
            'success' => true,
            'data' => $asociaciones
        ]);
    }

    public function index(Request $request)
    {
        $query = Reciclador::with('asociacion');
    
        if ($request->filled('search')) {
            $search = $request->input('search');
    
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%");
            });
        }
    
        $recicladores = $query->paginate(10)->withQueryString();
    
        return Inertia::render('Reciclador/index', [
            'recicladores' => $recicladores,
        ]);
    }
    

    public function createReciclador()
    {
        $asociaciones = Asociacion::all();
        $ciudades     = City::all();   // ← obtén todas las ciudades
        return Inertia::render('Reciclador/Create', [
            'asociaciones' => $asociaciones,
            'ciudades'     => $ciudades,
        ]);
    }

    public function storeReciclador(Request $request)
    {
        try {
            $data = $request->validate([
                'name'          => 'required|string',
                'telefono'      => 'required|string',
                'ciudad'        => 'required|string',
                'asociacion_id' => 'required|exists:asociaciones,id',
                'email'         => 'required|email|unique:auth_users,email',
                'password'      => 'required|string|min:8',
                //'status'        => 'required|in:disponible,en_ruta,inactivo',
                'estado'        => 'required|in:Activo,Inactivo',
            ]);

            // Crear el reciclador
            $reciclador = Reciclador::create([
                'name'          => $data['name'],
                'telefono'      => $data['telefono'],
                'ciudad'        => $data['ciudad'],
                'asociacion_id' => $data['asociacion_id'],
                'status'        => 'inactivo', 
                'estado'        => $data['estado'],
            ]);

            // Crear el usuario asociado
            AuthUser::create([
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'role'       => 'reciclador',
                'profile_id' => $reciclador->id,
            ]);

            // Redirigir al índice con mensaje
            return Redirect::route('reciclador.index')
                ->with('message', 'Reciclador y usuario creados exitosamente');

        } catch (\Illuminate\Validation\ValidationException $ve) {
            // Si falló la validación, Inertia ya envía automáticamente los errores
            throw $ve;
        } catch (\Exception $e) {
            // En caso de otro error, volver al formulario con mensaje genérico
            $asociaciones = Asociacion::all();
            return Inertia::render('Reciclador/Create', [
                'asociaciones' => Asociacion::all(),
                'ciudades'     => City::all(),
                'error'        => $e->getMessage(),
            ]);
        }
    }
    /**
     * Obtener todas las asignaciones del reciclador autenticado
     */


     public function deleteReciclador($id)
{
    try {
        // Buscar el reciclador por ID
        $reciclador = Reciclador::findOrFail($id);

        // Eliminar el reciclador
        $reciclador->delete();

        // Retornar respuesta de éxito
        return response()->json([
            'success' => true,
            'message' => 'Reciclador eliminado exitosamente.',
        ]);
    } catch (\Exception $e) {
        // En caso de error, retornar un mensaje de error
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el reciclador: ' . $e->getMessage(),
        ], 500);
    }
}

public function editReciclador($id)
{
    // Obtener el reciclador por ID
    $reciclador = Reciclador::findOrFail($id);

    // Obtener las asociaciones y ciudades
    $asociaciones = Asociacion::all();
    $ciudades = City::all();

    return Inertia::render('Reciclador/Edit', [
        'reciclador' => $reciclador,
        'asociaciones' => $asociaciones,
        'ciudades' => $ciudades
    ]);
}


public function updateReciclador(Request $request, $id)
{
    try {
        // Validar los datos recibidos
        $data = $request->validate([
            'name' => 'required|string',
            'telefono' => 'required|string',
            'ciudad' => 'required|string',
            'asociacion_id' => 'required|exists:asociaciones,id',
            'estado' => 'required|in:Activo,Inactivo',
        ]);

        // Buscar el reciclador por ID
        $reciclador = Reciclador::findOrFail($id);

        // Actualizar los datos del reciclador
        $reciclador->update([
            'name' => $data['name'],
            'telefono' => $data['telefono'],
            'ciudad' => $data['ciudad'],
            'asociacion_id' => $data['asociacion_id'],
            'estado' => $data['estado'],
        ]);

        // Responder con éxito
        return response()->json(['success' => true, 'message' => 'Reciclador actualizado con éxito']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al actualizar reciclador', 'error' => $e->getMessage()], 500);
    }
}



    // En un controlador como RecicladorController.php
    public function Pendientes(Request $request)
        {
            Log::info('el request es: ' . json_encode($request->all()));

            $query = SolicitudRecoleccion::where('reciclador_id', Auth::id())
                ->select([
                    'id',
                    'fecha',
                    'hora_inicio',
                    'hora_fin',
                    'latitud',
                    'longitud',
                    'peso_total',
                    'estado',
                    'user_id',
                    'foto_ubicacion',
                ])
                ->with([
                    'authUser:id,email,profile_id',
                    'authUser.ciudadano:id,name,logo_url,telefono'
                ])
                ->where(function($q) use ($request) {
                    // Siempre incluir los estados "asignado", "en_camino", "pendiente"
                    $q->whereIn('estado', ['asignado', 'en_camino', 'pendiente']);

                    // O incluir los que cumplen el filtro de fechas (y no están en esos estados)
                    if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
                        $q->orWhere(function($sub) use ($request) {
                            if ($request->filled('fecha_inicio')) {
                                $sub->whereDate('created_at', '>=', $request->fecha_inicio);
                            }
                            if ($request->filled('fecha_fin')) {
                                $sub->whereDate('created_at', '<=', $request->fecha_fin);
                            }
                            // Excluir los estados "asignado", "en_camino", "pendiente"
                            $sub->whereNotIn('estado', ['asignado', 'en_camino', 'pendiente']);
                        });
                    }
                });

            $solicitudes = $query->latest()->get()->map(function ($solicitud) {
                return [
                    'id' => $solicitud->id,
                    'fecha' => $solicitud->fecha,
                    'hora_inicio' => $solicitud->hora_inicio,
                    'hora_fin' => $solicitud->hora_fin,
                    'latitud' => $solicitud->latitud,
                    'longitud' => $solicitud->longitud,
                    'peso_total' => $solicitud->peso_total,
                    'estado' => $solicitud->estado,
                    'foto_ubicacion' => $solicitud->foto_ubicacion,
                    'ciudadano' => [
                        'name' => $solicitud->authUser?->ciudadano?->name,
                        'logo_url' => $solicitud->authUser?->ciudadano?->logo_url,
                        'email' => $solicitud->authUser?->email,
                        'telefono' => $solicitud->authUser?->ciudadano?->telefono,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $solicitudes
            ]);
        }

    public function obtenerContadorPendientes(Request $request)
    {
        try {
            $recicladorId = Auth::id();

            // Query optimizada que solo cuenta, no trae todos los datos
            $contador = SolicitudRecoleccion::where('reciclador_id', $recicladorId)
                ->whereIn('estado', ['pendiente', 'asignado', 'en_camino']) // Estados que consideras "pendientes"
                ->count();
            //verificar si algo tiene en_camino
            $camino = SolicitudRecoleccion::where('reciclador_id', $recicladorId)
                ->where('estado', 'en_camino')
                ->exists();
            Log::info('Contador de pendientes obtenido', [
                'reciclador_id' => $recicladorId,
                'contador' => $contador,
                'camino' => $camino
            ]);

            return response()->json([
                'success' => true,
                'contador' => $contador,
                'camino'=> $camino,
                'message' => 'Contador de pendientes obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contador de pendientes: ' . $e->getMessage(),
                'contador' => 0
            ], 500);
        }
    }
    public function Historial(Request $request)
    {
        // Inicializa la consulta base con el usuario autenticado
        $query = SolicitudRecoleccion::where('reciclador_id', Auth::id())
            ->where('estado', '!=', 'completado');

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

    public function obtenerAsignaciones()
    {
        Log::info('====== INICIO OBTENER ASIGNACIONES ======');

        try {
            // Obtener el usuario autenticado
            $authUser = Auth::user();
            Log::info('Usuario autenticado', [
                'id' => $authUser->id,
                'email' => $authUser->email,
                'role' => $authUser->role
            ]);

         
            // Primero, veamos todas las solicitudes con estado 'buscando_reciclador'
            $solicitudesBuscandoReciclador = SolicitudRecoleccion::where('estado', 'buscando_reciclador')->get();
            Log::info('Total solicitudes buscando_reciclador', [
                'count' => $solicitudesBuscandoReciclador->count()
            ]);

            // Verificar cada solicitud con estado 'buscando_reciclador'
            foreach ($solicitudesBuscandoReciclador as $solicitud) {
                $idsDisponibles = json_decode($solicitud->ids_disponibles, true) ?? [];
                Log::info('Verificando solicitud', [
                    'solicitud_id' => $solicitud->id,
                    'estado' => $solicitud->estado,
                    'ids_disponibles_raw' => $solicitud->ids_disponibles,
                    'ids_disponibles_decoded' => $idsDisponibles,
                    'usuario_esta_en_ids' => in_array($authUser->id, $idsDisponibles),
                    'tipo_usuario_id' => gettype($authUser->id),
                    'tipo_ids_array' => array_map('gettype', $idsDisponibles)
                ]);
            }

            // Solicitudes asignadas directamente
            $asignadasDirectamente = SolicitudRecoleccion::where('reciclador_id', $authUser->id)
                ->whereIn('estado', ['asignado', 'en_camino'])
                ->get();

            Log::info('Asignadas directamente', [
                'count' => $asignadasDirectamente->count(),
                'ids' => $asignadasDirectamente->pluck('id')->toArray()
            ]);

            // Ahora intentemos la consulta con JSON_CONTAINS
            try {
                $conJsonContains = SolicitudRecoleccion::where('estado', 'buscando_reciclador')
                    ->whereRaw('JSON_CONTAINS(ids_disponibles, ?)', ['"' . $authUser->id . '"'])
                    ->get();

                Log::info('Resultado con JSON_CONTAINS', [
                    'count' => $conJsonContains->count(),
                    'query' => 'JSON_CONTAINS(ids_disponibles, "' . $authUser->id . '")'
                ]);
            } catch (\Exception $jsonException) {
                Log::error('Error con JSON_CONTAINS', [
                    'error' => $jsonException->getMessage()
                ]);
            }

            // Método alternativo: filtrar en PHP
            $solicitudesConUsuarioEnIds = $solicitudesBuscandoReciclador->filter(function ($solicitud) use ($authUser) {
                $idsDisponibles = json_decode($solicitud->ids_disponibles, true) ?? [];

                // Intentar diferentes comparaciones
                $contieneIdString = in_array((string)$authUser->id, array_map('strval', $idsDisponibles));
                $contieneIdInt = in_array((int)$authUser->id, array_map('intval', $idsDisponibles));
                $contieneIdDirecto = in_array($authUser->id, $idsDisponibles);

                Log::info('Comparación detallada', [
                    'solicitud_id' => $solicitud->id,
                    'user_id' => $authUser->id,
                    'user_id_type' => gettype($authUser->id),
                    'ids_disponibles' => $idsDisponibles,
                    'contiene_string' => $contieneIdString,
                    'contiene_int' => $contieneIdInt,
                    'contiene_directo' => $contieneIdDirecto
                ]);

                return $contieneIdString || $contieneIdInt || $contieneIdDirecto;
            });

            Log::info('Solicitudes filtradas en PHP', [
                'count' => $solicitudesConUsuarioEnIds->count(),
                'ids' => $solicitudesConUsuarioEnIds->pluck('id')->toArray()
            ]);

            // Combinar todas las asignaciones
            $todasLasAsignaciones = $asignadasDirectamente->merge($solicitudesConUsuarioEnIds);

            // Cargar relaciones
            $asignaciones = $todasLasAsignaciones->load(['authUser', 'authUser.ciudadano', 'materiales'])
                ->sortByDesc('created_at')
                ->values();

            Log::info('====== RESULTADO FINAL ======', [
                'total_asignaciones' => $asignaciones->count(),
                'asignadas_directamente' => $asignadasDirectamente->count(),
                'en_ids_disponibles' => $solicitudesConUsuarioEnIds->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones obtenidas con éxito',
                'data' => $asignaciones,
                'debug' => [
                    'user_id' => $authUser->id,
                    'total_buscando_reciclador' => $solicitudesBuscandoReciclador->count(),
                    'asignadas_directamente' => $asignadasDirectamente->count(),
                    'en_ids_disponibles' => $solicitudesConUsuarioEnIds->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener asignaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerAsignacionPorId($id)
    {
        Log::info('Obteniendo asignación por ID: ' . $id);

        try {
            $authUser = Auth::user();
            Log::info('Usuario autenticado ID: ' . $authUser->id);

            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden ver asignaciones',
                ], 403);
            }

            // Simplemente obtener la solicitud por ID, sin restricciones de reciclador
            $solicitud = SolicitudRecoleccion::with(['authUser', 'authUser.ciudadano', 'materiales'])
                ->where('id', $id)
                ->first();

            Log::info('Asignación encontrada: ' . ($solicitud ? 'Sí' : 'No'));

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener asignación por ID: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignación',
            ], 500);
        }
    }

    public function obtenerDetalleAsignacion($id)
    {
        try {
            // Obtener el usuario autenticado (debe ser un reciclador)
            $authUser = Auth::user();
            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden ver detalles de asignaciones',
                ], 403);
            }

            // Obtener la solicitud y verificar que pertenezca a este reciclador
            $asignacion = SolicitudRecoleccion::where('id', $id)->where('reciclador_id', $authUser->id)
                ->with(['authUser.ciudadano', 'materiales'])
                ->first();

            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada o no pertenece a este reciclador',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detalle obtenido con éxito',
                'data' => $asignacion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function actualizarEstado(Request $request, $id)
    {
        //imprimir request por log
        Log::info('Actualizando estado de asignación: ' . $id);
        Log::info('Datos recibidos: ' . json_encode($request->all()));
        try {
            // Validar los datos de entrada
            $request->validate([
                'estado' => 'required|string',
            ]);

            // Obtener el usuario autenticado
            $authUser = Auth::user();

            // Buscar la solicitud
            $solicitud = SolicitudRecoleccion::find($id);

            //ver si el reciclador tiene alguna socilitud en camino
          /* $solicitudEnCamino= SolicitudRecoleccion::where('reciclador_id', $authUser->id)
                ->where('estado', 'en_camino')
                ->exists();
                if($solicitud->es_inmediata==1){if ($solicitudEnCamino) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una solicitud en camino, no puedes tomar otra',
                ], 409); // Cambiado a 409 (Conflict) que es más apropiado
            }} */
            

            // Verificar si la solicitud existe
            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe esa solicitud',
                ], 404); // Cambiado a 404 (Not Found) que es más apropiado
            }
            //imprimir el estadp del requst
            Log::info('Estado recibido: ' . $request->estado . ' y el estado de la solicitud es: ' . $solicitud->estado);

            // Manejar el estado "en_camino" esto para es inmediata la solciitud
            if ($request->estado === 'encamino' && $solicitud->estado === 'buscando_reciclador') {
                $solicitud->estado = 'en_camino';
                $solicitud->reciclador_id = $authUser->id;
                $solicitud->save();

                // Disparar eventos para eliminar solicitudes
                $ids_disponibles = json_decode($solicitud->ids_disponibles);
                foreach ($ids_disponibles as $id_disponible) {
                    EliminacionSolicitud::dispatch($solicitud, $id_disponible);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada a en camino correctamente',
                    'solicitud' => $solicitud
                ], 200);
            }
            // Manejar el estado "pendiente"
            else if ($request->estado === 'pendiente' && $solicitud->estado === 'en_camino') {
                //estado de la solciitud para proceder a revisar los materiales y decir que ya llegamos
                $solicitud->estado = $request->estado;
                $solicitud->reciclador_id = $authUser->id;
                $solicitud->save();
                EliminacionSolicitud::dispatch($solicitud, $authUser->id);
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada a pendiente correctamente',
                    'solicitud' => $solicitud
                ], 200);
            } else if ($request->estado === 'encamino' && $solicitud->estado === 'asignado') {
                //aqui para comenzar una solicitud ya asiganada
                $solicitud->estado = 'en_camino';
                $solicitud->reciclador_id = $authUser->id;
                $solicitud->save();
                EliminacionSolicitud::dispatch($solicitud, $authUser->id);
                //enviar notificacion
                FirebaseService::sendNotification($solicitud->user_id, [
                    'title' => 'El recolector está en camino',
                    'body' => 'El recolector inició a recoger tus materiales',
                    'data' => [
                        'route' => '/reciclador_asignado',
                        'solicitud_id' => (string)$solicitud->id,
                    ]
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada a pendiente correctamente',
                    'solicitud' => $solicitud
                ], 200);
            } else if ($solicitud->estado === 'buscando_reciclador' && $request->estado === 'asignado') {
                //esto es cuando la solicitud es agenda y guarda al 
                $solicitud->estado = $request->estado;
                $solicitud->reciclador_id = $authUser->id;
                $solicitud->save();

                $ids_disponibles = json_decode($solicitud->ids_disponibles);
                foreach ($ids_disponibles as $id_disponible) {
                    EliminacionSolicitud::dispatch($solicitud, $id_disponible);
                }
                //obtener el nombre del reciclador
                $reciclador = AuthUser::where('id', $solicitud->reciclador_id)->first();
                $nombreReciclador = reciclador::where('id', $reciclador->profile_id)->first();

                //enviar notificacion
                FirebaseService::sendNotification($solicitud->user_id, [
                    'title' => $nombreReciclador->name . ' acepto tu solcitud agendada',
                    'body' => 'Puedes ver los detalles ahora',
                    'data' => [
                        'route' => '/detalle_solicitud_ciudadano',
                        'solicitud_id' => (string)$solicitud->id,
                    ] // Este campo es opcional pero recomendado
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud actualizada a pendiente correctamente',
                    'solicitud' => $solicitud
                ], 200); // Código 409 (Conflict) es más apropiado
            }
            // Si la solicitud ya fue tomada
            else if ($solicitud->estado === 'en_camino' && $request->estado === 'en_camino') {
                EliminacionSolicitud::dispatch($solicitud, $authUser->id);

                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya fue tomada',
                ], 409); // Código 409 (Conflict) es más apropiado
            } else if ($solicitud->estado === 'asignado' && $request->estado === 'asignado') {
                EliminacionSolicitud::dispatch($solicitud, $authUser->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya fue tomada',
                ], 409); // Código 409 (Conflict) es más apropiado
            }
            // Cualquier otra combinación de estados no válida
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cambio de estado no permitido: ' . $solicitud->estado . ' -> ' . $request->estado,
                ], 400); // Bad Request para estados inválidos
            }
        } catch (ValidationException $e) {
            // Error específico para validación
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity para errores de validación
        } catch (\Exception $e) {
            // Cualquier otro error
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar el estado del reciclador
     */
    public function getEstado()
    {
        try {
            $usuario = Auth::user();



            // Obtener el estado actual del reciclador
            $estado = Reciclador::where('id', $usuario->profile_id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Estado obtenido correctamente',
                'data' => [
                    'estado' => $estado->status,
                    'ultima_actualizacion' => $estado->update_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado del reciclador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $data = $request->validate([
                'status' => 'required|in:disponible,en_ruta,inactivo',
            ]);

            $user = $request->user();
            $reciclador = Reciclador::find($user->profile_id);

            $reciclador->status = $data['status'];
            $reciclador->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'data' => $reciclador
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validarCambioEstadoRequest(Request $request)
    {

        try {
            $usuario = Auth::user();


            // Validar los datos de entrada
            $request->validate([
                'estado' => 'required|string|in:disponible,en_ruta,inactivo',
            ]);

            $validacion = $this->validarCambioEstado($usuario->id, $request->estado);

            return response()->json([
                'success' => true,
                'message' => $validacion['mensaje'],
                'data' => [
                    'puede_cambiar' => $validacion['puede_cambiar']
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar el cambio de estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método privado para validar si un reciclador puede cambiar su estado
     *
     * @param int $recicladorId
     * @param string $nuevoEstado
     * @return array
     */
    private function validarCambioEstado($recicladorId, $nuevoEstado)
    {
        // Verificar si hay solicitudes en_camino asignadas al reciclador
        $solicitudesEnCamino = SolicitudRecoleccion::where('reciclador_id', $recicladorId)
            ->where('estado', 'en_camino')
            ->count();

        if ($solicitudesEnCamino > 0 && $nuevoEstado == 'inactivo') {
            return [
                'puede_cambiar' => false,
                'mensaje' => 'No puedes cambiar a estado inactivo mientras tengas solicitudes en camino'
            ];
        }

        if ($solicitudesEnCamino > 0 && $nuevoEstado == 'disponible') {
            return [
                'puede_cambiar' => false,
                'mensaje' => 'No puedes cambiar a estado disponible mientras este realizando una solicitud'
            ];
        }
        // Si pasa todas las validaciones, puede cambiar su estado
        return [
            'puede_cambiar' => true,
            'mensaje' => 'Puedes cambiar tu estado'
        ];
    }
    /**
     * Actualizar revisión de materiales y calificación
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizarRevisionMateriales(Request $request, $id)
    {

        Log::info('Datos recibidos para revisión de materiales: ' . json_encode($request->all()));
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'materiales' => 'required|array',
                'materiales.*.id' => 'required|exists:materiales,id',
                'materiales.*.tipo' => 'required|string',
                'materiales.*.peso' => 'required|numeric|min:0',
                'peso_total' => 'required|numeric|min:0',
                'calificacion' => 'required|integer|min:1|max:50',
                'comentario_reciclador' => 'nullable|string|max:255',
                
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener la solicitud
            $solicitud = SolicitudRecoleccion::find($id);

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // Verificar que la solicitud pertenezca al reciclador
            if ($solicitud->reciclador_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar esta solicitud'
                ], 403);
            }

            // Verificar que la solicitud esté en un estado válido para revisión
            if (!in_array($solicitud->estado, ['pendiente', 'en_camino'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede revisar una solicitud en estado ' . $solicitud->estado
                ], 400);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Actualizar peso total revisado
                $solicitud->peso_total_revisado = $request->peso_total;
                $solicitud->calificacion_ciudadano = $request->calificacion;
                $solicitud->estado = 'completado';
                $solicitud->fecha_completado = now();
                $solicitud->comentario_reciclador = $request->comentario_reciclador; // <-- Agrega esta línea
                $solicitud->save();

             // Actualizar materiales
                foreach ($request->materiales as $materialData) {
                    $material = Material::find($materialData['id']);

                    // Verificar que el material pertenezca a la solicitud
                    if ($material && $material->solicitud_id == $id) {
                        $material->peso_revisado = $materialData['peso'];
                        $material->reciclador_id = $user->id;
                        $material->save();
                    }
                }
                //agregar puntos al ciudadano
                $user_ciudadano = AuthUser::find($solicitud->user_id);
                $user_ciudadano->puntos +=$request->calificacion;
                $user_ciudadano->save();

                //agregar puntos extras si tiene solciitud completas 3 completadas dar 50 puntos si tiene 5 dar 100 y si tiene mas de 10 dar 200
                $solicitudesCompletadas = SolicitudRecoleccion::where('user_id', $solicitud->user_id)
                    ->where('estado', 'completado')
                    ->count();

                if ($solicitudesCompletadas == 10) {
                    $user_ciudadano->puntos += 200;
                    //enviar notificacion al ciudadano
                    FirebaseService::sendNotification($solicitud->user_id, [
                        'title' => '¡Felicidades!',
                        'body' => 'Has completado 10 solicitudes y has recibido 200 puntos adicionales.',
                        'data' => [
                            'route' => '/historial_solicitudes',
                            'solicitud_id' => (string)$solicitud->id,
                        ]
                    ]);
                } elseif ($solicitudesCompletadas == 5) {
                    $user_ciudadano->puntos += 100;
                    //enviar notificacion al ciudadano
                    FirebaseService::sendNotification($solicitud->user_id, [
                        'title' => '¡Buen trabajo!',
                        'body' => 'Has completado 5 solicitudes y has recibido 100 puntos adicionales.',
                        'data' => [
                            'route' => '/historial_solicitudes',
                            'solicitud_id' => (string)$solicitud->id,
                        ]
                    ]);
                } elseif ($solicitudesCompletadas == 3) {
                    $user_ciudadano->puntos += 50;
                    //enviar notificacion al ciudadano
                    FirebaseService::sendNotification($solicitud->user_id, [
                        'title' => '¡Buen trabajo!',
                        'body' => 'Has completado 3 solicitudes y has recibido 50 puntos adicionales.',
                        'data' => [
                            'route' => '/historial_solicitudes',
                            'solicitud_id' => (string)$solicitud->id,
                        ]
                    ]);
                }
                $user_ciudadano->save();

                DB::commit();
                
                //evento para actualizar los puntos del ciudadano actual
                event(new ActualizarPuntosCiudadano($user_ciudadano->id, $user_ciudadano->puntos));
                //emitir el evento que se completo
                event(new SolicitudCompleto($solicitud->id));

                return response()->json([
                    'success' => true,
                    'message' => 'Revisión de materiales y calificación guardadas correctamente'
                ]);
            } catch (\Exception $e) {
                // Revertir transacción en caso de error
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar revisión de materiales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar revisión de materiales: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarRevisionMaterialesunico(Request $request, $id)
    {

        Log::info('Datos recibidos unicossssss: ' . json_encode($request->all()));
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'materiales' => 'required|array',
                'materiales.*.id' => 'required|exists:materiales,id',
                'materiales.*.tipo' => 'required|string',
                'materiales.*.peso' => 'required|numeric|min:0',
                'peso_total' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener la solicitud
            $solicitud = SolicitudRecoleccion::find($id);

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // Verificar que la solicitud pertenezca al reciclador
            if ($solicitud->reciclador_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar esta solicitud'
                ], 403);
            }

            // Iniciar transacción
            DB::beginTransaction();

            try {
                // Actualizar peso total revisado
                $solicitud->peso_total_revisado = $request->peso_total;
                $solicitud->save();

             // Actualizar materiales
                foreach ($request->materiales as $materialData) {
                    $material = Material::find($materialData['id']);

                    // Verificar que el material pertenezca a la solicitud
                    if ($material && $material->solicitud_id == $id) {
                        $material->peso_revisado = $materialData['peso'];
                        $material->reciclador_id = $user->id;
                        $material->save();
                    }
                }

                // Confirmar transacción
                DB::commit();
                //emitir el evento que se completo
                event(new SolicitudCompleto($solicitud->id));

                return response()->json([
                    'success' => true,
                    'message' => 'Revisión de materiales guardadas correctamente'
                ]);
            } catch (\Exception $e) {
                // Revertir transacción en caso de error
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar revisión de materiales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar revisión de materiales: ' . $e->getMessage()
            ], 500);
        }
       
    }

    /**
     * Sincronizar asignaciones al abrir la aplicación
     * Este método obtiene solicitudes nuevas desde la última sincronización
     */
    public function sincronizarAsignaciones(Request $request)
    {
        Log::info('====== INICIO SINCRONIZACIÓN ASIGNACIONES ======');

        try {
            $authUser = Auth::user();

            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden sincronizar asignaciones',
                ], 403);
            }

            // Obtener información del reciclador
            $reciclador = Reciclador::find($authUser->profile_id);
            $ultimaSincronizacion = $reciclador->ultima_sincronizacion;

            Log::info('Sincronización de reciclador', [
                'reciclador_id' => $authUser->id,
                'ultima_sincronizacion' => $ultimaSincronizacion
            ]);

            // DETECTAR SI ES RECONEXIÓN RECIENTE O SINCRONIZACIÓN INICIAL
            $esReconexionReciente = false;
            $esPrimeraSincronizacion = !$ultimaSincronizacion;

            if ($ultimaSincronizacion) {
                $minutosDesdeUltima = now()->diffInMinutes($ultimaSincronizacion);
                $esReconexionReciente = $minutosDesdeUltima < 10; // Menos de 10 minutos = reconexión
            }

            Log::info('Tipo de sincronización', [
                'es_primera_sincronizacion' => $esPrimeraSincronizacion,
                'es_reconexion_reciente' => $esReconexionReciente,
                'minutos_desde_ultima' => $ultimaSincronizacion ? now()->diffInMinutes($ultimaSincronizacion) : 'N/A'
            ]);

            // OBTENER SOLICITUDES BUSCANDO RECICLADOR
            $queryBuscandoReciclador = SolicitudRecoleccion::where('estado', 'buscando_reciclador');

            // Solo filtrar por fecha si NO es reconexión reciente y NO es primera vez
            if (!$esReconexionReciente && !$esPrimeraSincronizacion) {
                $queryBuscandoReciclador->where('created_at', '>', $ultimaSincronizacion);
            }

            $solicitudesBuscandoReciclador = $queryBuscandoReciclador->get();

            Log::info('Solicitudes buscando reciclador encontradas', [
                'count' => $solicitudesBuscandoReciclador->count(),
                'aplicado_filtro_fecha' => (!$esReconexionReciente && !$esPrimeraSincronizacion)
            ]);

            // FILTRAR SOLICITUDES DONDE ESTÁ EN IDS_DISPONIBLES
            $solicitudesDisponibles = $solicitudesBuscandoReciclador->filter(function ($solicitud) use ($authUser) {
                $idsDisponibles = json_decode($solicitud->ids_disponibles, true) ?? [];
                return in_array($authUser->id, $idsDisponibles) ||
                    in_array((string)$authUser->id, array_map('strval', $idsDisponibles));
            });

            Log::info('Solicitudes donde el reciclador está disponible', [
                'count' => $solicitudesDisponibles->count(),
                'ids' => $solicitudesDisponibles->pluck('id')->toArray()
            ]);

            // LÓGICA DE NOTIFICACIÓN MEJORADA
            if ($esReconexionReciente || $esPrimeraSincronizacion) {
                // CASO 1: Reconexión reciente o primera vez - devolver todas las disponibles
                $solicitudesParaNotificar = $solicitudesDisponibles;

                Log::info('Caso: Reconexión/Primera vez - incluyendo todas las disponibles', [
                    'count' => $solicitudesParaNotificar->count()
                ]);
            } else {
                // CASO 2: Sincronización normal - filtrar por no notificadas
                $solicitudesParaNotificar = $solicitudesDisponibles->filter(function ($solicitud) use ($authUser) {
                    $recicladoresNotificados = json_decode($solicitud->recicladores_notificados, true) ?? [];
                    $yaNotificado = in_array($authUser->id, $recicladoresNotificados) ||
                        in_array((string)$authUser->id, array_map('strval', $recicladoresNotificados));
                    return !$yaNotificado;
                });

                Log::info('Caso: Sincronización normal - filtrando no notificadas', [
                    'count' => $solicitudesParaNotificar->count()
                ]);
            }

            // MARCAR COMO NOTIFICADAS (solo en sincronización normal, no en reconexiones)
            if (!$esReconexionReciente) {
                foreach ($solicitudesParaNotificar as $solicitud) {
                    $recicladoresNotificados = json_decode($solicitud->recicladores_notificados, true) ?? [];

                    if (!in_array($authUser->id, $recicladoresNotificados)) {
                        $recicladoresNotificados[] = $authUser->id;
                        $solicitud->recicladores_notificados = json_encode($recicladoresNotificados);
                        $solicitud->save();

                        Log::info('Solicitud marcada como notificada', [
                            'solicitud_id' => $solicitud->id,
                            'recicladores_notificados' => $recicladoresNotificados
                        ]);
                    }
                }
            } else {
                Log::info('Reconexión reciente - NO marcando como notificadas para preservar estado');
            }

            // CARGAR RELACIONES
            $solicitudesParaNotificar->load(['authUser', 'authUser.ciudadano', 'materiales']);

            // ORDENAR RESULTADOS
            $asignacionesOrdenadas = $solicitudesParaNotificar
                ->sortByDesc('created_at')
                ->values();

            // ACTUALIZAR ÚLTIMA SINCRONIZACIÓN
            $reciclador->ultima_sincronizacion = now();
            $reciclador->save();

            Log::info('====== RESULTADO SINCRONIZACIÓN ======', [
                'total_asignaciones' => $asignacionesOrdenadas->count(),
                'solicitudes_para_notificar' => $solicitudesParaNotificar->count(),
                'tipo_sincronizacion' => $esReconexionReciente ? 'reconexion' : ($esPrimeraSincronizacion ? 'primera' : 'normal'),
                'nueva_sincronizacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronización completada con éxito',
                'data' => $asignacionesOrdenadas,
                'sync_info' => [
                    'timestamp' => now(),
                    'nuevas_solicitudes' => $solicitudesParaNotificar->count(),
                    'asignaciones_directas' => 0,
                    'es_primera_sincronizacion' => $esPrimeraSincronizacion,
                    'es_reconexion_reciente' => $esReconexionReciente,
                    'tipo_sincronizacion' => $esReconexionReciente ? 'reconexion' : ($esPrimeraSincronizacion ? 'primera' : 'normal')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en sincronización de asignaciones: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marcar solicitud como vista por el reciclador
     * Se llama cuando el reciclador ve la solicitud pero no la acepta aún
     */
    public function marcarSolicitudVista(Request $request, $solicitudId)
    {
        try {
            $authUser = Auth::user();

            $solicitud = SolicitudRecoleccion::find($solicitudId);

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // Marcar como notificado si no lo está
            $recicladoresNotificados = json_decode($solicitud->recicladores_notificados, true) ?? [];

            if (!in_array($authUser->id, $recicladoresNotificados)) {
                $recicladoresNotificados[] = $authUser->id;
                $solicitud->recicladores_notificados = json_encode($recicladoresNotificados);
                $solicitud->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud marcada como vista'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información de sincronización del reciclador
     */
    public function obtenerInfoSincronizacion(Request $request)
    {
        try {
            $authUser = Auth::user();

            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden acceder a esta información',
                ], 403);
            }

            $reciclador = Reciclador::find($authUser->profile_id);

            // Contar solicitudes pendientes para este reciclador
            $solicitudesPendientes = SolicitudRecoleccion::where('estado', 'buscando_reciclador')
                ->whereRaw('JSON_CONTAINS(ids_disponibles, ?)', ['"' . $authUser->id . '"'])
                ->whereRaw('NOT JSON_CONTAINS(IFNULL(recicladores_notificados, "[]"), ?)', ['"' . $authUser->id . '"'])
                ->count();

            // Contar asignaciones directas
            $asignacionesDirectas = SolicitudRecoleccion::where('reciclador_id', $authUser->id)
                ->whereIn('estado', ['asignado', 'en_camino'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'reciclador_id' => $authUser->id,
                    'ultima_sincronizacion' => $reciclador->ultima_sincronizacion,
                    'solicitudes_pendientes_no_notificadas' => $solicitudesPendientes,
                    'asignaciones_directas' => $asignacionesDirectas,
                    'total_disponibles' => $solicitudesPendientes + $asignacionesDirectas,
                    'necesita_sincronizacion' => $reciclador->necesitaSincronizacion(30), // 30 minutos
                    'solicitudes_notificadas' => $reciclador->solicitudes_notificadas ?? []
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener info de sincronización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de sincronización',
            ], 500);
        }
    }
    public function obtenerEstadisticas(Request $request)
    {
        try {
            $authUser = Auth::user();

            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden acceder a esta información',
                ], 403);
            }

            // Contar solicitudes pendientes para este reciclador// Calcula las estadísticas (ejemplo)
            $totalRecolecciones = SolicitudRecoleccion::where('reciclador_id', $authUser->id)->count();
            $recoleccionesEsteMes = SolicitudRecoleccion::where('reciclador_id', $authUser->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            return response()->json([
                'success' => true,
                'data' => [
                    'recolecciones' => $totalRecolecciones,
                    'este_mes' => $recoleccionesEsteMes,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener info de sincronización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de sincronización',
            ], 500);
        }
    }
    public function cancelar_solicitud_reciclador(Request $request)
    {

        $request->validate([
            'solicitud_id' => 'required|integer|exists:solicitudes_recoleccion,id',
            'motivo' => 'required|string|max:255',
            'comentario' => 'nullable|string|max:500',
        ]);

        $solicitud = SolicitudRecoleccion::find($request->solicitud_id);

        // Opcional: Verifica que el usuario autenticado sea el dueño o tenga permisos
        if ($solicitud->reciclador_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado para cancelar esta solicitud.',
            ], 403);
        }
        

        // Cambia el estado y guarda el motivo
        $solicitud->estado = 'cancelado';
        $solicitud->comentarios = '(Cancelado por el reciclador) Motivo: '.$request->motivo.' '.$request->comentario;
        $solicitud->save();

        //enviar evento aviso
        event(new CancelarSolicitudReciclador($solicitud, $request->motivo, $request->comentario));
        Log::info('evento enviado');

        //enviar notificacion al ciudadano
        FirebaseService::sendNotification($solicitud->user_id, [
            'title' => 'Solicitud cancelada',
            'body' => 'El reciclador ha cancelado la solicitud de recolección.',
            'data' => [
                'route' => '/detalle_solicitud_ciudadano',
                'solicitud_id' => (string)$solicitud->id,
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada con éxito',
        ]);
    }
}
