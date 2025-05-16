<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Models\Solicitud;
use App\Models\Reciclador;
use App\Models\Asociacion;
use Illuminate\Support\Facades\DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AsociacionController extends Controller
{
    /**
     * Obtener todos los recicladores de la asociación autenticada
     */
    public function getRecicladores(Request $request)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            // Parámetros de paginación
            $perPage = $request->query('per_page', 5);
            $page = $request->query('page', 1);

            // Parámetro de búsqueda
            $busqueda = $request->query('busqueda', '');

            // Query base
            $query = Reciclador::where('asociacion_id', $asociacion->id);

            // Aplicar filtro de búsqueda si existe
            if (!empty($busqueda)) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%");
                });

                // Si es búsqueda, limitar a 3 resultados
                if ($request->query('es_busqueda', false)) {
                    $perPage = 3;
                }
            }

            // Obtener resultados paginados
            $recicladores = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Recicladores obtenidos correctamente',
                'data' => $recicladores->items(),
                'pagination' => [
                    'current_page' => $recicladores->currentPage(),
                    'last_page' => $recicladores->lastPage(),
                    'per_page' => $recicladores->perPage(),
                    'total' => $recicladores->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recicladores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las solicitudes asignadas a la asociación
     */
    public function getSolicitudes(Request $request)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            $solicitudes = Solicitud::with(['ciudadano:id,name,telefono,direccion', 'reciclador:id,name,telefono,status'])
                ->where('asociacion_id', $asociacion->id)
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
     * Asignar una solicitud a un reciclador
     */
    public function asignarSolicitud(Request $request)
    {
        try {
            $data = $request->validate([
                'solicitud_id' => 'required|exists:solicitudes,id',
                'reciclador_id' => 'required|exists:recicladores,id',
                'fecha_recoleccion' => 'required|date',
            ]);

            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            // Verificar que la solicitud pertenezca a esta asociación
            $solicitud = Solicitud::where('id', $data['solicitud_id'])
                ->where('asociacion_id', $asociacion->id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada o no pertenece a esta asociación'
                ], 404);
            }

            // Verificar que el reciclador pertenezca a esta asociación
            $reciclador = Reciclador::where('id', $data['reciclador_id'])
                ->where('asociacion_id', $asociacion->id)
                ->first();

            if (!$reciclador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reciclador no encontrado o no pertenece a esta asociación'
                ], 404);
            }

            // Verificar que el reciclador esté disponible
            if ($reciclador->status !== 'disponible') {
                return response()->json([
                    'success' => false,
                    'message' => 'El reciclador no está disponible actualmente'
                ], 400);
            }

            // Verificar que la solicitud no esté ya asignada o completada
            if ($solicitud->status !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud ya ha sido asignada o completada'
                ], 400);
            }

            // Asignar la solicitud
            $solicitud->reciclador_id = $reciclador->id;
            $solicitud->fecha_recoleccion = $data['fecha_recoleccion'];
            $solicitud->status = 'asignada';
            $solicitud->save();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud asignada correctamente',
                'data' => $solicitud
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
                'message' => 'Error al asignar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de la asociación
     */
    public function getEstadisticas(Request $request)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            // Contar recicladores por estado
            $recicladoresStats = Reciclador::where('asociacion_id', $asociacion->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status')
                ->toArray();

            // Contar solicitudes por estado
            $solicitudesStats = Solicitud::where('asociacion_id', $asociacion->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status')
                ->toArray();

            // Contar solicitudes por mes (últimos 6 meses)
            $solicitudesPorMes = Solicitud::where('asociacion_id', $asociacion->id)
                ->whereDate('created_at', '>=', now()->subMonths(6))
                ->selectRaw('MONTH(created_at) as mes, YEAR(created_at) as anio, count(*) as total')
                ->groupBy('mes', 'anio')
                ->orderBy('anio')
                ->orderBy('mes')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'recicladores' => $recicladoresStats,
                    'solicitudes' => $solicitudesStats,
                    'solicitudes_por_mes' => $solicitudesPorMes,
                    'total_recicladores' => Reciclador::where('asociacion_id', $asociacion->id)->count(),
                    'total_solicitudes' => Solicitud::where('asociacion_id', $asociacion->id)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil de la asociación
     */
    public function updateProfile(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'number_phone' => 'sometimes|string|max:20',
                'direccion' => 'sometimes|string|max:255',
                'descripcion' => 'sometimes|string',
            ]);

            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            $asociacion->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => $asociacion
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
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una solicitud
     */
    public function cancelarSolicitud(Request $request, $id)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            $solicitud = Solicitud::where('id', $id)
                ->where('asociacion_id', $asociacion->id)
                ->first();

            if (!$solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solicitud no encontrada o no pertenece a esta asociación'
                ], 404);
            }

            // Verificar que la solicitud no esté completada
            if ($solicitud->status === 'completada') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cancelar una solicitud completada'
                ], 400);
            }

            // Cancelar la solicitud
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


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $asociacionData = $request->validate([
                'name' => 'required|string|max:255',
                'number_phone' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                'logo_url' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'verified' => 'nullable|boolean',
                'color' => 'nullable|string|max:10',
            ]);

            $userData = $request->validate([
                'email' => 'required|email|unique:auth_users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($request->hasFile('logo_url')) {
                $path = $request->file('logo_url')->store('asociaciones', 'public');
                $asociacionData['logo_url'] = 'storage/' . $path;
            }

            $asociacion = Asociacion::create($asociacionData);

            $user = AuthUser::create([
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'role' => 'asociacion',
                'profile_id' => $asociacion->id,
                'fcm_token' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('asociation.index')
                ->with('success', 'Asociación y usuario creados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creando asociación: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'Error al crear la asociación: ' . $e->getMessage())->withInput();
        }
    }


    public function eliminarAsociacion($id)
    {
        DB::beginTransaction();

        try {
            $asociacion = Asociacion::find($id);

            if (!$asociacion) {
                return redirect()->route('asociation.index')
                    ->with('error', 'Asociación no encontrada');
            }

            $user = AuthUser::where('profile_id', $asociacion->id)
                ->where('role', 'asociacion')
                ->first();

            if ($user) {
                $user->delete();
            }

            $asociacion->delete();

            DB::commit();

            return redirect()->route('asociation.index')
                ->with('success', 'Asociación eliminada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('asociation.index')
                ->with('error', 'Error al eliminar la asociación: ' . $e->getMessage());
        }
    }
}
