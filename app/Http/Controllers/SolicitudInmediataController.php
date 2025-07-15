<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SolicitudRecoleccion;
use App\Models\Reciclador;
use App\Models\Material;
use App\Models\Ubicacionreciladores;
use App\Models\AuthUser;
use Carbon\Carbon;
use App\Services\FirebaseService;
use App\Events\NuevaSolicitudInmediata;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ActualizarRecicladoresdisponibles;
use App\Jobs\CancelarSolicitudInmediata;

//websocket


class SolicitudInmediataController extends Controller
{
    /**
     * Busca recicladores cercanos y crea una solicitud inmediata
     */
    public function buscarRecicladores(Request $request)
    {
        try {
            // Validar los datos de la solicitud
            $validatedData = $request->validate([
                'direccion' => 'required|string|max:255',
                'referencia' => 'required|string|max:255',
                'latitud' => 'required|numeric',
                'longitud' => 'required|numeric',
                'peso_total' => 'required|numeric|min:0.1',
                'materiales' => 'required|json',
                'imagenes.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'es_inmediata' => 'required',
                'foto_ubicacion' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $validatedData['es_inmediata'] = filter_var($validatedData['es_inmediata'], FILTER_VALIDATE_BOOLEAN);

            // Obtener el usuario autenticado (ciudadano)
            $user = Auth::user();

            $rutasImagenes = [];
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $index => $imagen) {
                    $nombreImagen = time() . '_' . $index . '_' . Auth::id() . '.' . $imagen->getClientOriginalExtension();
                    $rutaImagen = $imagen->storeAs('solicitudes', $nombreImagen, 'public');
                    $rutasImagenes[] = $rutaImagen;
                }
            }

            // Guardar la foto de ubicación si existe
            $rutaFotoUbicacion = null;
            if ($request->hasFile('foto_ubicacion')) {
                $fotoUbicacion = $request->file('foto_ubicacion');
                $nombreFotoUbicacion = time() . '_ubicacion_' . Auth::id() . '.' . $fotoUbicacion->getClientOriginalExtension();
                $rutaFotoUbicacion = $fotoUbicacion->storeAs('solicitudes', $nombreFotoUbicacion, 'public');
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Crear la solicitud
            $solicitud = SolicitudRecoleccion::create([
                'user_id' => Auth::id(),
                'asociacion_id' => null,
                'fecha' => Carbon::now()->format('Y-m-d'),
                'hora_inicio' => Carbon::now()->format('H:i'),
                'hora_fin' => Carbon::now()->addMinutes(30)->format('H:i'),
                'direccion' => $validatedData['direccion'],
                'referencia' => $validatedData['referencia'],
                'latitud' => $validatedData['latitud'],
                'longitud' => $validatedData['longitud'],
                'peso_total' => $validatedData['peso_total'],
                'imagen' => json_encode($rutasImagenes),
                'foto_ubicacion' => $rutaFotoUbicacion,
                'estado' => 'buscando_reciclador',
                'ciudad' => $user->ciudadano->ciudad ?? 'No especificada',
                'es_inmediata' => $validatedData['es_inmediata'],
                'ids_disponibles' => json_encode([]), // Empezar vacío
            ]);

            // Guardar materiales
            $materialesData = json_decode($validatedData['materiales'], true);
            foreach ($materialesData as $material) {
                Material::create([
                    'solicitud_id' => $solicitud->id,
                    'tipo' => $material['tipo'],
                    'peso' => $material['peso'],
                    'user_id' => Auth::id(),
                ]);
            }



            // ✅ SOLO ESTO - Delegar toda la búsqueda al job
            Log::info('Iniciando búsqueda de recicladores para solicitud', [
                'solicitud_id' => $solicitud->id,
                'latitud' => $solicitud->latitud,
                'longitud' => $solicitud->longitud
            ]);
            Log::info('ciudad del ciudadano'. $user->ciudadano->ciudad ?? 'No especificada');
            // Iniciar búsqueda inmediatamente con el job
            ActualizarRecicladoresdisponibles::dispatch($solicitud->id, 0, 16, 3, $user->ciudadano->ciudad ?? 'No especificada')
                ->delay(now()); // Sin delay - empezar inmediatamente

           


            DB::commit();
             // 🆕 NUEVO: Programar cancelación automática después de 5 minutos
            CancelarSolicitudInmediata::dispatch($solicitud->id)->delay(now()->addMinutes(5));
            

            // Devolver respuesta optimista
            return response()->json([
                'success' => true,
                'message' => 'Buscando recicladores cercanos... Te notificaremos cuando encontremos algunos.',
                'data' => [
                    'solicitud_id' => $solicitud->id,
                    'tiempo_espera_maximo' => 4, // minutos
                    'estado' => 'buscando_reciclador',
                    'mensaje_adicional' => 'La búsqueda puede tomar unos momentos. Mantén la aplicación abierta para recibir notificaciones.'
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear solicitud inmediata', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encuentra recicladores cercanos usando la fórmula Haversine
     */
    private function encontrarRecicladoresCercanos($latitud, $longitud, $radioKm = 3, $limite = 10)
    {


        // Convertir radio de km a metros para Redis
        $radioMetros = $radioKm * 1000;

        // Buscar recicladores cercanos usando Redis GEORADIUS
        $recicladorIds = Redis::georadius(
            'recycler:locations:active',
            $longitud,
            $latitud,
            $radioMetros,
            'm',
            ['WITHDIST', 'ASC', 'COUNT' => $limite * 2] // Obtenemos más para filtrar después
        );

        if (empty($recicladorIds)) {

            if ($radioKm < 10) {
                return $this->encontrarRecicladoresCercanos($latitud, $longitud, 10, $limite);
            }
            return collect([]);
        }



        // Procesar resultados de Redis
        $recicladoresFiltrados = collect();
        foreach ($recicladorIds as $item) {
            // Corregir la forma en que accedemos a los datos según la estructura real
            // La estructura puede ser diferente dependiendo de tu versión de Redis y phpredis

            // Caso 1: Si el resultado es un array plano [userId, distancia]
            if (is_array($item) && isset($item[0]) && isset($item[1])) {
                $userId = $item[0];
                $distanciaMetros = (float)$item[1];
            }
            // Caso 2: Si el resultado es un string (solo el ID)
            else if (is_string($item)) {
                $userId = $item;
                $distanciaMetros = 0; // No tenemos la distancia

                // Puedes calcular la distancia manualmente si es necesario
                // usando la fórmula Haversine
            }
            // Caso 3: Para otra estructura, necesitamos conocer el formato exacto
            else {

                continue;
            }
        }



        // Si no encontramos suficientes, intentar con radio mayor
        if ($recicladoresFiltrados->isEmpty() && $radioKm < 10) {

            return $this->encontrarRecicladoresCercanos($latitud, $longitud, 10, $limite);
        }

        return $recicladoresFiltrados->values();
    }

    /**
     * Envía notificación al reciclador (implementar según tu sistema de notificaciones)
     */


    /**
     * Verifica el estado actual de una solicitud inmediata
     */
    public function verificarEstado($id)
    {
        $solicitud = SolicitudRecoleccion::findOrFail($id);

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
        ];

        // No necesitas nada más si no vas a mostrar datos del reciclador

        return response()->json($respuesta);
    }


    /**
     * Calcula la distancia entre dos puntos usando Haversine
     */
}
