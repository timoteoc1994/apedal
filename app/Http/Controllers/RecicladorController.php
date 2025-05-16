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

class RecicladorController extends Controller
{
    /**
     * Obtener todas las asignaciones del reciclador autenticado
     */
    // En un controlador como RecicladorController.php

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
        //imprimir lo que viene
        Log::info('Actualizando estado de asignación: ' . $id);
        Log::info('Datos recibidos: ' . json_encode($request->all()));
        try {
            // Validar los datos de entrada
            $request->validate([
                'estado' => 'required|string',
            ]);

            // Obtener el usuario autenticado (debe ser un reciclador)
            $authUser = Auth::user();
            if ($authUser->role !== 'reciclador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo los recicladores pueden actualizar estados de asignaciones',
                ], 403);
            }

            //manejar para el estado buscando reciclador
            //log para el estado buscando reciclador
            $solicitud = SolicitudRecoleccion::find($id);
            Log::info('Ahora vamos a actualizar la solicitud a en camino actualmente esta en: ' . $solicitud->estado);
            if ($request->estado === 'encamino' && $solicitud->estado === 'buscando_reciclador') {
                //imprimir que le tomamos
                Log::info('Le tomamos la solicitud con id: ' . $id);
                if ($solicitud) {
                    $solicitud->estado = 'en_camino';
                    $solicitud->reciclador_id = $authUser->id;
                    $solicitud->save();

                    log::info('Dispara evento a solicitud con id:' . $id . ' y para el usuario con id' . $authUser->id);
                    //disparar el NuevaSolicitudInmediata evento para todos los ids_disponibles que es json

                    //disparar el evento NuevaSolicitudInmediata para todos los ids_disponibles que es json
                    $ids_disponibles = json_decode($solicitud->ids_disponibles);
                    foreach ($ids_disponibles as $id_disponible) {
                        log::info('Disparando el evento para eliminar la solicitud de los otros usuarios');
                        EliminacionSolicitud::dispatch($solicitud, $id_disponible);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Actualizado correctamente',
                    ]);
                }
                //eliminamos de pronto no haygan aceptado
                $solicitud = SolicitudRecoleccion::find($id);
                EliminacionSolicitud::dispatch($solicitud, Auth::user()->id);
                return response()->json([
                    'success' => false,
                    'message' => 'No existe esa solicitud',
                ], 500);
            } else {
                //eliminamos de pronto no haygan aceptado
                $solicitud = SolicitudRecoleccion::find($id);
                EliminacionSolicitud::dispatch($solicitud, Auth::user()->id);
                Log::info('No le tomamos la solicitud con id: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya fue tomada',
                ], 500);
            }
        } catch (\Exception $e) {
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
}
