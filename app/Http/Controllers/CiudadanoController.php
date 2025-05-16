<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Ciudadano;
use App\Models\Asociacion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CiudadanoController extends Controller
{
    /**
     * Obtener todas las solicitudes del ciudadano autenticado
     */
    public function getSolicitudes(Request $request)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            $solicitudes = Solicitud::with(['asociacion:id,name,number_phone', 'reciclador:id,name,telefono'])
                ->where('ciudadano_id', $ciudadano->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Solicitudes obtenidas correctamente',
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitudes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una solicitud específica
     */
    public function getSolicitud(Request $request, $id)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            $solicitud = Solicitud::with(['asociacion:id,name,number_phone', 'reciclador:id,name,telefono'])
                ->where('ciudadano_id', $ciudadano->id)
                ->where('id', $id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Solicitud obtenida correctamente',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva solicitud de recolección
     */
    public function createSolicitud(Request $request)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            $data = $request->validate([
                'direccion' => 'required|string',
                'ciudad' => 'required|string',
                'referencias' => 'nullable|string',
                'materiales' => 'required|string',
                'comentarios' => 'nullable|string',
                'fecha_solicitud' => 'required|date',
                'asociacion_id' => 'required|exists:asociaciones,id',
            ]);

            // Asegurarse de que la asociación exista
            $asociacion = Asociacion::find($data['asociacion_id']);
            if (!$asociacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'La asociación seleccionada no existe'
                ], 404);
            }

            // Crear la solicitud
            $solicitud = new Solicitud([
                'ciudadano_id' => $ciudadano->id,
                'asociacion_id' => $data['asociacion_id'],
                'direccion' => $data['direccion'],
                'ciudad' => $data['ciudad'],
                'referencias' => $data['referencias'] ?? null,
                'materiales' => $data['materiales'],
                'comentarios' => $data['comentarios'] ?? null,
                'fecha_solicitud' => $data['fecha_solicitud'],
                'status' => 'pendiente',
            ]);

            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud creada correctamente',
                'data' => $solicitud
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una solicitud
     */
    public function cancelSolicitud(Request $request, $id)
    {
        try {
            $user = $request->user();
            $ciudadano = Ciudadano::find($user->profile_id);

            $solicitud = Solicitud::where('ciudadano_id', $ciudadano->id)
                ->where('id', $id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // Verificar si la solicitud puede ser cancelada
            if ($solicitud->status === 'completada') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cancelar una solicitud completada'
                ], 400);
            }

            $solicitud->status = 'cancelada';
            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud cancelada correctamente',
                'data' => $solicitud
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las asociaciones disponibles
     */
    public function getAsociaciones()
    {
        try {
            $asociaciones = Asociacion::where('verified', true)
                ->select('id', 'name', 'number_phone', 'city')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Asociaciones obtenidas correctamente',
                'data' => $asociaciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asociaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
