<?php

namespace App\Http\Controllers;

use App\Models\Ciudadano;
use App\Models\SolicitudRecoleccion;
use GPBMetadata\Google\Api\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log as FacadesLog;

class RankingController extends Controller
{
    public function index()
    {
        try {
            $anio_actual = date('Y');
        $mes_actual = date('m');
            $solicitudes = SolicitudRecoleccion::select('user_id')
                ->selectRaw('SUM(peso_total_revisado) as total_peso')
                ->where('estado', 'completado')
                ->whereYear('created_at', $anio_actual)
            ->whereMonth('created_at', $mes_actual)
                ->groupBy('user_id')
                ->orderBy('total_peso', 'desc')
                ->take(10)
                ->get();

            // Formatear los datos para la vista
            $ranking = $solicitudes->map(function ($solicitud, $index) {
                $ciudadano = Ciudadano::select('nickname', 'ciudad', 'logo_url')
                    ->where('id', $solicitud->user_id)
                    ->first();

                $respuesta = [
                    'user_id' => (int) $solicitud->user_id,
                    'nombre' => $ciudadano ? $ciudadano->nickname : 'Usuario',
                    'puntos' => (float) $solicitud->total_peso, // ← Convertir a float
                    'avatar' => $ciudadano ? $ciudadano->logo_url : null,
                    'ciudad' => $ciudadano ? $ciudadano->ciudad : 'Sin ciudad',
                    'posicion' => $index + 1,
                ];

                return $respuesta;
            });
            FacadesLog::info($ranking);
            return response()->json(['data' => $ranking]);
        } catch (\Exception $e) {
            // Manejo de error si no hay usuario autenticado
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder al ranking.');
        }
    }
}
