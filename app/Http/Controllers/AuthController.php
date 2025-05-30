<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Models\Ciudadano;
use App\Models\Reciclador;
use App\Models\Asociacion;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;


class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            // Validar datos comunes
            $common = $request->validate([
                'email' => 'required|email|unique:auth_users,email',
                'password' => 'required|min:8|confirmed',
                'role' => 'required|in:ciudadano,reciclador,asociacion',
                'fcm_token' => 'nullable|string', // Añadir validación para token FCM
            ]);

            // Validar datos específicos según el rol
            $profileData = [];
            $profile = null;

            if ($common['role'] === 'ciudadano') {
                $profileData = $request->validate([
                    'name' => 'required|string',
                    'direccion' => 'required|string',
                    'ciudad' => 'required|string',
                    'telefono' => 'nullable|string',
                    'referencias_ubicacion' => 'nullable|string',
                ]);

                $profile = Ciudadano::create($profileData);
            } elseif ($common['role'] === 'reciclador') {
                $profileData = $request->validate([
                    'name' => 'required|string',
                    'telefono' => 'required|string',
                    'ciudad' => 'required|string',
                    'asociacion_id' => 'required|exists:asociaciones,id',
                ]);

                $profile = Reciclador::create($profileData);
            } elseif ($common['role'] === 'asociacion') {
                $profileData = $request->validate([
                    'name' => 'required|string',
                    'number_phone' => 'required|string',
                    'city' => 'required|string',
                    'direccion' => 'nullable|string',
                    'descripcion' => 'nullable|string',
                ]);

                $profile = Asociacion::create($profileData);
            }

            // Crear usuario de autenticación con el token FCM
            $userData = [
                'email' => $common['email'],
                'password' => Hash::make($common['password']),
                'role' => $common['role'],
                'profile_id' => $profile->id,
            ];

            // Añadir el token FCM si está presente
            if ($request->has('fcm_token') && $request->fcm_token) {
                $userData['fcm_token'] = $request->fcm_token;
            }

            $user = AuthUser::create($userData);

            // Generar token si se usa Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso',
                'data' => [
                    'user' => $user,
                    'profile' => $profile,
                    'token' => $token
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() == 23505 || $e->getCode() == 1062) { // Códigos para clave duplicada en PostgreSQL y MySQL
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function solo_reciclador_register(Request $request)
    {
        try {
            // Validar datos comunes
            $common = $request->validate([
                'email' => 'required|email|unique:auth_users,email',
                'password' => 'required|min:8|confirmed',
                'role' => 'required|in:ciudadano,reciclador,asociacion',
                'fcm_token' => 'nullable|string', // Añadir validación para token FCM
            ]);

            // Validar datos específicos según el rol
            $profileData = [];
            $profile = null;;


            if ($common['role'] === 'reciclador') {
                $profileData = $request->validate([
                    'name' => 'required|string',
                    'telefono' => 'required|string',
                    'asociacion_id' => 'required|exists:asociaciones,id',
                ]);
                //anadir variables a $profileData
                //buscar ciudad de la asociacion
                $asociacion = Asociacion::find($request->asociacion_id);
                $id_de_auth = AuthUser::where('profile_id', $asociacion->id)->where('role', 'asociacion')->first();

                $profileData['asociacion_id'] =  $id_de_auth->id;
                $profileData['ciudad'] = $asociacion->city;
                $profileData['estado'] = 'Inactivo';
                $profileData['status'] = 'inactivo';


                $profile = Reciclador::create($profileData);
            }

            // Crear usuario de autenticación con el token FCM
            $userData = [
                'email' => $common['email'],
                'password' => Hash::make($common['password']),
                'role' => $common['role'],
                'profile_id' => $profile->id,
            ];

            // Añadir el token FCM si está presente
            if ($request->has('fcm_token') && $request->fcm_token) {
                $userData['fcm_token'] = $request->fcm_token;
            }

            $user = AuthUser::create($userData);

            // Generar token si se usa Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso',
                'data' => [
                    'user' => $user,
                    'profile' => $profile,
                    'token' => $token
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            // Manejo de errores de base de datos
            if ($e->getCode() == 23505 || $e->getCode() == 1062) { // Códigos para clave duplicada en PostgreSQL y MySQL
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login de usuarios
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'fcm_token' => 'nullable|string', // Añadir validación para el token FCM
            ]);

            $user = AuthUser::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            // Actualizar el token FCM si se proporciona
            if ($request->has('fcm_token') && $request->fcm_token) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            // Obtener datos específicos del perfil
            $profileData = null;
            if ($user->role === 'ciudadano') {
                $profileData = Ciudadano::find($user->profile_id);
            } elseif ($user->role === 'reciclador') {
                $profileData = Reciclador::with('asociacion:id,name')->find($user->profile_id);
                // Verificar si la cuenta está habilitada
                if (!$profileData || $profileData->estado != 'Activo') {
                    return response()->json([
                        'success' => false,
                        'message' => 'La cuenta del reciclador aún no ha sido habilitada'
                    ], 403);
                }
            } elseif ($user->role === 'asociacion') {
                $profileData = Asociacion::find($user->profile_id);
                // Verificar si la cuenta está habilitada
                if (!$profileData || $profileData->verified != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La cuenta de la asociación aún no ha sido habilitada'
                    ], 403);
                }
            }

            // Generar token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'user' => $user,
                    'profile' => $profileData,
                    'token' => $token
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout de usuarios
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            //eliminar el token fmc de la base de datos
            $request->user()->fcm_token = null;
            $request->user()->save();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registro de reciclador (solo para asociaciones)
     */
    public function registerRecycler(Request $request)
    {
        try {
            // Verificar que el usuario autenticado sea una asociación
            if ($request->user()->role !== 'asociacion') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para registrar recicladores'
                ], 403);
            }

            // Datos de validación
            $data = $request->validate([
                'email' => 'required|email|unique:auth_users,email',
                'password' => 'required|min:8|confirmed',
                'name' => 'required|string',
                'telefono' => 'required|string',
                'ciudad' => 'required|string',
            ]);

            // Obtener ID de la asociación actual
            $asociacionId = $request->user()->profile_id;

            // Crear reciclador
            $reciclador = Reciclador::create([
                'name' => $data['name'],
                'telefono' => $data['telefono'],
                'ciudad' => $data['ciudad'],
                'asociacion_id' => $asociacionId,
                'status' => 'disponible',
            ]);

            // Crear usuario de autenticación
            $user = AuthUser::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'reciclador',
                'profile_id' => $reciclador->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reciclador registrado correctamente',
                'data' => [
                    'user' => $user,
                    'profile' => $reciclador,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            if ($e->getCode() == 23505 || $e->getCode() == 1062) { // Códigos para clave duplicada
                return response()->json([
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado'
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ciudades disponibles
     */
    public function getCities()
    {
        try {
            $cities = City::all(['id', 'name']);

            return response()->json([
                'success' => true,
                'message' => 'Ciudades obtenidas correctamente',
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asociaciones disponibles
     */
    public function getAsociaiones()
    {
        try {
            $asociaciones = Asociacion::where('verified', 1)->get(['id', 'name']);


            return response()->json([
                'success' => true,
                'message' => 'Ciudades obtenidas correctamente',
                'data' => $asociaciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudades',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtener perfil del usuario autenticado
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            $profileData = null;

            if ($user->role === 'ciudadano') {
                $profileData = Ciudadano::find($user->profile_id);
            } elseif ($user->role === 'reciclador') {
                $profileData = Reciclador::with('asociacion:id,name')->find($user->profile_id);
            } elseif ($user->role === 'asociacion') {
                $profileData = Asociacion::find($user->profile_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'data' => [
                    'user' => $user,
                    'profile' => $profileData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
