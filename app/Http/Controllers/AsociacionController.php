<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Models\Solicitud;
use App\Models\Reciclador;
use App\Models\Asociacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\SolicitudRecoleccion;

class AsociacionController extends Controller
{
    /**
     * Obtener todos los recicladores de la asociación autenticada
     */
   public function activarcuentaReciclador(Request $request, $id)
{
    Log::info('Activando cuenta de reciclador con ID: ' . $id);
    try {
        $reciclador = Reciclador::find($id);
        if (!$reciclador) {
            return response()->json([
                'success' => false,
                'message' => 'Reciclador no encontrado'
            ], 404);
        }
        if ($reciclador->asociacion_id !== $request->user()->profile_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para activar este reciclador'
            ]);
        }

        $reciclador->is_new = false;
        $reciclador->estado = 'Activo';
        $reciclador->save();
        Log::info('Reciclador activado: ' . $reciclador->name);

        //enviar correo de bienvenida al reciclador
        $auth_user = AuthUser::where('profile_id', $reciclador->id)
            ->where('role', 'reciclador')
            ->first();
        
        if ($auth_user && $auth_user->email) {
            \Illuminate\Support\Facades\Mail::raw(
            "¡Bienvenido a la aplicación Adri!\n\nTu cuenta ha sido activada correctamente. Ahora puedes iniciar sesión y comenzar a usar la plataforma.\n\nSaludos,\nEquipo Adri",
            function ($message) use ($auth_user) {
                $message->to($auth_user->email)
                ->subject('¡Tu cuenta ha sido activada en Adri!');
            }
            );
        }
   

        return response()->json([
            'success' => true,
            'message' => 'Cuenta de reciclador activada correctamente',
            'data' => $reciclador
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al activar reciclador',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function updateReciclador(Request $request, $id)
    {
        Log::warning($request->all());
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'estado' => 'required|string',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
                ],
                'password_confirmation' => 'sometimes|string|min:8',
                'email' => 'sometimes|string|email|max:255',
            ], [
                'password.regex' => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un carácter especial.'
            ]);

            $reciclador = Reciclador::find($id);
            if (!$reciclador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reciclador no encontrado'
                ], 404);
            }
            if ($reciclador->asociacion_id !== $request->user()->profile_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar este reciclador'
                ]);
            }

            //actualizar solo datos del perfil
            if ($request->filled('name')) {
                $reciclador->name = $data['name'];
            }
            if ($request->filled('phone')) {
                $reciclador->telefono = $data['phone'];
            }
            if ($request->filled('estado')) {
                $reciclador->estado = $data['estado'];
            }
            $reciclador->is_new = 'false';
            $reciclador->update($data);

            //actualizar el email y contrasena si existen
            $auth_user = AuthUser::where('profile_id', $reciclador->id)->where('role', 'reciclador')->first();
            Log::warning($auth_user->email);
            if ($auth_user) {
                Log::warning($auth_user->email);

                if ($request->filled('email')) {
                    $auth_user->email = $data['email'];
                }

                if ($request->filled('password')) {
                    $auth_user->password = $data['password'];
                }

                $auth_user->save(); // Guardas una sola vez si algo cambió
            }
            return response()->json([
                'success' => true,
                'message' => 'Reciclador actualizado correctamente',
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
                'message' => 'Error al actualizar reciclador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getRecicladores(Request $request)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            // Parámetros de paginación
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            // Parámetro de búsqueda
            $busqueda = $request->query('busqueda', '');

            // Query base
            $query = Reciclador::where('asociacion_id', $asociacion->id)->where('is_new', 'false');

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

    public function getRecicladoresnuevos(Request $request)
    {
        try {
            $user = $request->user();
            $asociacion = Asociacion::find($user->profile_id);

            // Parámetros de paginación
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            // Parámetro de búsqueda
            $busqueda = $request->query('busqueda', '');

            // Query base
            $query = Reciclador::where('asociacion_id', $asociacion->id)->where('is_new', 'true');

            // Aplicar filtro de búsqueda si existe
            if (!empty($busqueda)) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%");
                });

                // Si es búsqueda, limitar a 3 resultados
                if ($request->query('es_busqueda', false)) {
                    $perPage = 5;
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
            Log::error('Error creando asociación: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
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
    public function obtenerEstadisticas(Request $request)
    {

        try {

            //obtener la id del authuser
            $authUser = AuthUser::where('profile_id', $request->reciclador_id)->where('role', 'reciclador')->first();
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
}
