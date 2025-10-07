<?php

namespace App\Http\Controllers;

use App\Models\Ubicacionreciladores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Events\LocationUpdate;
use Illuminate\Support\Facades\Log;
use App\Models\Reciclador;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Events\UbicacionActualizada;

class UbicacionreciladoresController extends Controller
{
    /**
     * Actualizar la ubicación del reciclador
     */
    public function updateLocation(Request $request)
    {
        $user = Auth::user();
        // Validar los datos recibidos
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'timestamp' => 'nullable|string', // Cambiar a string
        ]);

        // Convertir timestamp a Carbon
        if (isset($validated['timestamp'])) {
            $timestamp = Carbon::parse($validated['timestamp']);
        } else {
            $timestamp = Carbon::now();
        }

        // Obtener el estado del reciclador
        $reciclador = Reciclador::where('id', $user->profile_id)->first();
        $status = $reciclador->status ?? 'disponible';

        // 🆕 Cachear perfil del reciclador en Redis para búsquedas rápidas
        if ($reciclador) {
            $profileData = [
                'id' => $reciclador->id,
                'name' => $reciclador->name,
                'telefono' => $reciclador->telefono,
                'logo_url' => $reciclador->logo_url,
                'asociacion_id' => $reciclador->asociacion_id,
                'ciudad' => $reciclador->ciudad ?? 'No especificada',
                'status' => $status,
                'cached_at' => now()->timestamp
            ];
            Redis::setex("recycler:profile:{$user->id}", 3600, json_encode($profileData)); // Cache por 1 hora

            Log::debug('Perfil del reciclador cacheado en Redis', [
                'user_id' => $user->id,
                'reciclador_id' => $reciclador->id,
                'status' => $status,
                'ciudad' => $reciclador->ciudad ?? 'No especificada'
            ]);
        }

        // Datos de ubicación - aquí el timestamp ya es un objeto Carbon
        $locationData = [
            'auth_user_id' => $user->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'timestamp' => $timestamp->toIso8601String(), // Ahora sí funcionará
            'status' => $status,
            'ciudad' => $reciclador->ciudad ?? 'No especificada', // 🆕 Añadir ciudad
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        // 1. Guardar en Redis para acceso rápido
        Redis::setex(
            "recycler:location:{$user->id}",
            300, // Expira en 5 minutos
            json_encode($locationData)
        );
        $ubicacion = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ];

        // 2. Guardar en Redis GEO para búsquedas espaciales
        if ($status === 'disponible' || $status === 'en_ruta') {
            Redis::geoadd(
                'recycler:locations:active',
                $validated['longitude'],
                $validated['latitude'],
                $user->id
            );
        }

        // 3. Guardar estado del reciclador
        Redis::hset("recycler:status", $user->id, $status);


        // 4. Si el estado es inactivo, remover de ubicaciones activas
        if ($status === 'inactivo') {
            Redis::zrem('recycler:locations:active', $user->id);
        }

        // 5. Decidir si guardar en DB
        $shouldSaveToDb = false;
        $lastSaved = Redis::get("recycler:last_db_save:{$user->id}");

        if (!$lastSaved) {
            $shouldSaveToDb = true;
        } else {
            $lastData = json_decode($lastSaved, true);
            $distance = $this->calculateDistance(
                $lastData['latitude'],
                $lastData['longitude'],
                $validated['latitude'],
                $validated['longitude']
            );

            // Guardar si se movió más de 100 metros o pasaron 5 minutos
            $timeDiff = time() - strtotime($lastData['timestamp']);
            if ($distance > 100 || $timeDiff > 300) {
                $shouldSaveToDb = true;
            }
        }

        // Guardar en DB si es necesario
        if ($shouldSaveToDb) {
            $location = Ubicacionreciladores::updateOrCreate(
                ['auth_user_id' => $user->id],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'timestamp' => $timestamp,
                    'updated_at' => Carbon::now(),
                ]
            );

            // Actualizar última vez guardado en DB
            Redis::setex(
                "recycler:last_db_save:{$user->id}",
                600,
                json_encode($locationData)
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada correctamente',
            'saved_to_db' => $shouldSaveToDb,
            'data' => $locationData,
        ]);
    }


    /**
     * Calcular distancia entre dos puntos
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // metros

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    //buscarubiacaion actual del reciclador en redis
    public function ubiacacionActual(Request $request)
    {
        try {
            $user = Auth::user();

            // Primero intentar obtener de Redis (más rápido)
            $redisLocation = Redis::get("recycler:location:{$user->id}");

            if ($redisLocation) {
                $locationData = json_decode($redisLocation, true);

                return response()->json([
                    'success' => true,
                    'message' => 'Ubicación obtenida de caché',
                    'data' => [
                        'latitude' => (float) $locationData['latitude'],
                        'longitude' => (float) $locationData['longitude'],
                        'timestamp' => $locationData['timestamp'],
                        'status' => $locationData['status'],
                        'source' => 'redis'
                    ]
                ]);
            }

            

            // Si no hay ubicación guardada
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ubicación para el reciclador',
                'data' => null
            ], 404);
        } catch (Exception $e) {
            Log::error('Error al obtener ubicación del reciclador: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
