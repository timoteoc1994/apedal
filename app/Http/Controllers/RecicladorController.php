<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Reciclador;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\Log;
use App\Models\Ciudadano;
use App\Events\NuevaSolicitudInmediata;
use App\Events\EliminacionSolicitud;
use Illuminate\Support\Facades\DB;
use App\Models\Material;
//importar validator
use Illuminate\Support\Facades\Validator;
use App\Models\AuthUser;
use App\Services\FirebaseService;
use Carbon\Carbon;

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
    /**
     * Obtener todas las asignaciones del reciclador autenticado
     */

    // En un controlador como RecicladorController.php
    public function Pendientes(Request $request)
    {
        $solicitudes = SolicitudRecoleccion::where('reciclador_id', Auth::id())
            ->where('estado', '!=', 'completado')
            ->select([
                'id',
                'fecha',
                'hora_inicio',
                'hora_fin',
                'latitud',
                'longitud',
                'peso_total',
                'estado',
                'user_id' // Necesario para la relación
            ])
            ->with([
                'authUser:id,email,profile_id',
                'authUser.ciudadano:id,name,logo_url' // Agrega logo_url aquí
            ])
            ->latest()
            ->get()
            ->map(function ($solicitud) {
                return [
                    'id' => $solicitud->id,
                    'fecha' => $solicitud->fecha,
                    'hora_inicio' => $solicitud->hora_inicio,
                    'hora_fin' => $solicitud->hora_fin,
                    'latitud' => $solicitud->latitud,
                    'longitud' => $solicitud->longitud,
                    'peso_total' => $solicitud->peso_total,
                    'estado' => $solicitud->estado,
                    'ciudadano' => [
                        'name' => $solicitud->authUser?->ciudadano?->name,
                        'logo_url' => $solicitud->authUser?->ciudadano?->logo_url,
                        'email' => $solicitud->authUser?->email,
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $solicitudes
        ]);
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

            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden ver sus asignaciones',
                ], 403);
            }

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
            $asignacion = SolicitudRecoleccion::where('id', $id)
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
                    'title' => 'El recolector esta en camino',
                    'body' => 'El recolector inicio a recoger tus materiales, puedes verlo el mapa',
                    'data' => [] // Este campo es opcional pero recomendado
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
                    'title' => 'Tu solicitud agendada fue aceptada',
                    'body' => 'Puedes revisarla en tu historial, el reciclador asignado es ' . $nombreReciclador->name,
                    'data' => [] // Este campo es opcional pero recomendado
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
        //imprimir lo que llega
        Log::info('Datos recibidos: ' . json_encode($request->all()));
        //imprimir id
        Log::info('ID de la solicitud: ' . $id);
        try {
            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'materiales' => 'required|array',
                'materiales.*.id' => 'required|exists:materiales,id',
                'materiales.*.tipo' => 'required|string',
                'materiales.*.peso' => 'required|numeric|min:0',
                'peso_total' => 'required|numeric|min:0',
                'calificacion' => 'required|integer|min:1|max:5',
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

                // Actualizar calificación del ciudadano
                $solicitud->calificacion_ciudadano = $request->calificacion;

                // Actualizar el estado a completado
                $solicitud->estado = 'completado';
                $solicitud->fecha_completado = now();

                $solicitud->save();

                // Actualizar materiales
                foreach ($request->materiales as $materialData) {
                    $material = Material::find($materialData['id']);

                    // Verificar que el material pertenezca a la solicitud
                    if ($material && $material->solicitud_id == $id) {
                        $material->peso_revisado = $materialData['peso'];
                        $material->save();
                    }
                }

                // Confirmar transacción
                DB::commit();

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
}
