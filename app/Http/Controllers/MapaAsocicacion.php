<?php

namespace App\Http\Controllers;

use App\Models\Asociacion;
use App\Models\Ciudadano;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class MapaAsocicacion extends Controller
{
    public function getAsociaciones(Request $request): JsonResponse
    {
        try {
            $authUser = Auth::user();

            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            if ($authUser->role !== 'ciudadano') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo ciudadanos pueden acceder a esta función'
                ], 403);
            }

            $ciudadano = Ciudadano::where('id', $authUser->profile_id)->first();

            if (!$ciudadano) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de ciudadano no encontrado'
                ], 404);
            }

            $asociaciones = Asociacion::where('city', $ciudadano->ciudad)
                ->where('verified', true)
                ->select([
                    'id',
                    'name',
                    'number_phone',
                    'direccion',
                    'city',
                    'descripcion',
                    'logo_url',
                    'color',
                    'verified',
                    'imagen_referencial',
                    'dias_atencion',
                    'hora_apertura',
                    'hora_cierre',
                    'materiales_aceptados'
                ])
                ->get();

            if ($asociaciones->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => "No se encontraron asociaciones verificadas en {$ciudadano->ciudad}",
                    'data' => []
                ], 200);
            }

            $asociacionesConInfo = $asociaciones->map(function ($asociacion) {
                $coordenadas = $this->extraerCoordenadas($asociacion->direccion);

                return [
                    'id' => $asociacion->id,
                    'nombre' => $asociacion->name,
                    'descripcion' => $asociacion->descripcion ?? 'Asociación de reciclaje comprometida con el medio ambiente',
                    'direccion' => $asociacion->descripcion,
                    'ciudad' => $asociacion->city,
                    'telefono' => $asociacion->number_phone,
                    'email' => optional($asociacion->authUser)->email,
                    'imagen' => $asociacion->logo_url,
                    'color' => $asociacion->color ?? '#00AC5F',
                    'activo' => $asociacion->verified,
                    'latitud' => $coordenadas['latitud'],
                    'longitud' => $coordenadas['longitud'],
                    'imagen_referencial' => $asociacion->imagen_referencial,
                    'dias_atencion' => $asociacion->dias_atencion,
                    'hora_apertura' => $asociacion->hora_apertura,
                    'hora_cierre' => $asociacion->hora_cierre,
                    'materiales_aceptados' => $asociacion->materiales_aceptados,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Se encontraron {$asociacionesConInfo->count()} asociaciones en {$ciudadano->ciudad}",
                'data' => $asociacionesConInfo,
                'total' => $asociacionesConInfo->count(),
                'ciudad_ciudadano' => $ciudadano->ciudad
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error procesando solicitud'
            ], 500);
        }
    }


    /**
     * Extraer coordenadas del formato "Lat: -1.241340, Long: -78.629620"
     *
     * @param string $direccion
     * @return array
     */
    private function extraerCoordenadas(string $direccion): array
    {
        // Patrón para encontrar Lat: y Long: seguidos de números decimales
        $patron = '/Lat:\s*(-?\d+\.?\d*),?\s*Long:\s*(-?\d+\.?\d*)/i';

        if (preg_match($patron, $direccion, $matches)) {
            return [
                'latitud' => (float) $matches[1],
                'longitud' => (float) $matches[2]
            ];
        }

        // Si no se encuentran coordenadas, usar valores por defecto según la ciudad
        return [
            'latitud' => $this->getLatitudPorCiudad($this->extraerCiudadDeDireccion($direccion)),
            'longitud' => $this->getLongitudPorCiudad($this->extraerCiudadDeDireccion($direccion))
        ];
    }

    /**
     * Limpiar la dirección removiendo las coordenadas
     *
     * @param string $direccion
     * @return string
     */
    private function limpiarDireccion(string $direccion): string
    {
        // Remover el patrón de coordenadas de la dirección
        $patron = '/Lat:\s*(-?\d+\.?\d*),?\s*Long:\s*(-?\d+\.?\d*)/i';
        $direccionLimpia = preg_replace($patron, '', $direccion);

        // Limpiar espacios y comas extra
        $direccionLimpia = trim($direccionLimpia, ', ');
        $direccionLimpia = preg_replace('/\s+/', ' ', $direccionLimpia);

        return $direccionLimpia ?: 'Dirección no especificada';
    }

    /**
     * Extraer ciudad de la dirección (si está disponible)
     *
     * @param string $direccion
     * @return string
     */
    private function extraerCiudadDeDireccion(string $direccion): string
    {
        $ciudades = ['Ambato', 'Quito', 'Guayaquil', 'Cuenca', 'Riobamba', 'Latacunga', 'Baños'];

        foreach ($ciudades as $ciudad) {
            if (stripos($direccion, $ciudad) !== false) {
                return $ciudad;
            }
        }

        return 'Ambato'; // Por defecto
    }

    /**
     * Obtener latitud por ciudad (coordenadas de respaldo)
     */
    private function getLatitudPorCiudad(string $ciudad): float
    {
        $coordenadas = [
            'Ambato' => -1.2544,
            'Quito' => -0.1807,
            'Guayaquil' => -2.1894,
            'Cuenca' => -2.9001,
            'Riobamba' => -1.6635,
            'Latacunga' => -0.9346,
            'Baños' => -1.3928,
        ];

        return $coordenadas[$ciudad] ?? -1.2544;
    }

    /**
     * Obtener longitud por ciudad (coordenadas de respaldo)
     */
    private function getLongitudPorCiudad(string $ciudad): float
    {
        $coordenadas = [
            'Ambato' => -78.6267,
            'Quito' => -78.4678,
            'Guayaquil' => -79.8890,
            'Cuenca' => -79.0059,
            'Riobamba' => -78.6569,
            'Latacunga' => -78.6233,
            'Baños' => -78.4269,
        ];

        return $coordenadas[$ciudad] ?? -78.6267;
    }
}
