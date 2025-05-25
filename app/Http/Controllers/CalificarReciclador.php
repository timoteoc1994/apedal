<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\Auth;

class CalificarReciclador extends Controller
{
    public function calificarReciclador(Request $request)
    {
        // Validar datos
        $validator = Validator::make($request->all(), [
            'solicitud_id' => 'required|integer|exists:solicitudes_recoleccion,id',
            'calificacion' => 'required|integer|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Obtener la solicitud
            $solicitud = SolicitudRecoleccion::where('id', $request->solicitud_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la solicitud o no tienes permiso para calificar',
                ], 404);
            }

            // Validar que el estado sea "completado"
            if ($solicitud->estado !== 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes calificar solicitudes completadas',
                ], 400);
            }

            // Guardar calificación
            $solicitud->calificacion_reciclador = $request->calificacion;
            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Calificación enviada con éxito',
                'data' => [
                    'solicitud_id' => $solicitud->id,
                    'calificacion' => $solicitud->calificacion_reciclador,
                ],
            ], 200);
        } catch (\Exception $e) {


            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la calificación',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
