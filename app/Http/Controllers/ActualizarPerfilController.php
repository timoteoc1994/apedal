<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Ciudadano;
use Illuminate\Support\Facades\Storage;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;


class ActualizarPerfilController extends Controller
{
    /**
     * Actualizar perfil sin imagen
     */
    public function update(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            //actualizar perfil solo ciudadano
            if ($user->role == 'ciudadano') {
                // Validar los campos enviados
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'telefono' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'ciudad' => 'required|string',
                    'referencias_ubicacion' => 'nullable|string',
                ]);

                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();

                // Acceder al perfil del ciudadano usando la relación
                $ciudadano = $user->ciudadano;

                // Actualizar los datos del ciudadano
                $ciudadano->name = $validatedData['name'];
                $ciudadano->telefono = $validatedData['telefono'];
                $ciudadano->direccion = $validatedData['direccion'];
                $ciudadano->ciudad = $validatedData['ciudad'];
                $ciudadano->referencias_ubicacion = $validatedData['referencias_ubicacion'] ?? $ciudadano->referencias_ubicacion;
                $ciudadano->save();

                // Devolver respuesta exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente',
                    'data' => [
                        'user' => $user,
                        'profile' => $ciudadano
                    ]
                ]);
            } else if ($user->role === 'reciclador') {
                Log::warning('datos del usuario: ' . $user->toJson());
                // Validar los campos enviados
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'telefono' => 'required|string|max:15',
                ]);
                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();
                // Acceder al perfil del reciclador usando la relación
                $reciclador = $user->reciclador;
                // Actualizar los datos del ciudadano
                $reciclador->name = $validatedData['name'];
                $reciclador->telefono = $validatedData['telefono'];
                $reciclador->save();
                // Devolver respuesta exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente',
                    'data' => [
                        'user' => $user,
                        'profile' => $reciclador
                    ]
                ]);
            } else if ($user->role === 'asociacion') {
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar el perfil'
                ], 403);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
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
     * Actualizar perfil con imagen
     */
    public function updateWithImage(Request $request)
    {

        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            //actualizarperfil ciudadano
            if ($user->role === 'ciudadano') {
                // Validar los campos enviados
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'telefono' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'ciudad' => 'required|string',
                    'referencias_ubicacion' => 'nullable|string',
                    'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]);

                // Acceder al perfil del ciudadano
                $ciudadano = $user->ciudadano;

                // Procesar y guardar la imagen
                if ($request->hasFile('profile_image')) {
                    // Eliminar imagen anterior si existe
                    if ($ciudadano->logo_url) {
                        $oldPath = $ciudadano->logo_url;
                        $oldImagePath = public_path(ltrim($oldPath, '/')); // Quitar la barra inicial si existe
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    // Generar un nombre único para la imagen
                    $imageName = 'ciudadano_' . $ciudadano->id . '_' . time() . '.' . $request->profile_image->extension();

                    // Guardar la imagen en el almacenamiento
                    $request->profile_image->storeAs('profiles', $imageName, 'public');

                    // Guardar SOLO la ruta relativa para la imagen (sin el dominio)
                    $relativePath = '/storage/profiles/' . $imageName;
                }

                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();

                // Actualizar los datos del ciudadano
                $ciudadano->name = $validatedData['name'];
                $ciudadano->telefono = $validatedData['telefono'];
                $ciudadano->direccion = $validatedData['direccion'];
                $ciudadano->ciudad = $validatedData['ciudad'];
                $ciudadano->referencias_ubicacion = $validatedData['referencias_ubicacion'] ?? $ciudadano->referencias_ubicacion;

                // Actualizar URL de la imagen si se subió una nueva
                if (isset($relativePath)) {
                    $ciudadano->logo_url = $relativePath;
                }

                $ciudadano->save();

                // Devolver respuesta exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente con nueva imagen',
                    'data' => [
                        'user' => $user,
                        'profile' => $ciudadano
                    ]
                ]);
            } else if ($user->role === 'reciclador') {
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'telefono' => 'required|string|max:15',
                    'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]);
                $reciclador = $user->reciclador;
                if ($request->hasFile('profile_image')) {
                    // Eliminar imagen anterior si existe
                    if ($reciclador->logo_url) {
                        $oldPath = $reciclador->logo_url;
                        $oldImagePath = public_path(ltrim($oldPath, '/')); // Quitar la barra inicial si existe
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    // Generar un nombre único para la imagen
                    $imageName = 'reciclador_' . $reciclador->id . '_' . time() . '.' . $request->profile_image->extension();
                    // Guardar la imagen en el almacenamiento
                    $request->profile_image->storeAs('profiles', $imageName, 'public');
                    // Guardar SOLO la ruta relativa para la imagen (sin el dominio)
                    $relativePath = '/storage/profiles/' . $imageName;
                    // Actualizar el email del usuario
                    $user->email = $validatedData['email'];
                    $user->save();
                    // Actualizar los datos del ciudadano
                    $reciclador->name = $validatedData['name'];
                    $reciclador->telefono = $validatedData['telefono'];
                    // Actualizar URL de la imagen si se subió una nueva
                    if (isset($relativePath)) {
                        $reciclador->logo_url = $relativePath;
                    }
                    $reciclador->save();
                    // Devolver respuesta exitosa
                    return response()->json([
                        'success' => true,
                        'message' => 'Perfil actualizado correctamente con nueva imagen',
                        'data' => [
                            'user' => $user,
                            'profile' => $reciclador,
                        ]
                    ]);
                }
            } else if ($user->role === 'asociacion') {
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar el perfil'
                ], 403);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
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
     * Cambiar contraseña del usuario
     */
    public function changePassword(Request $request)
    {
        try {
            $user = auth()->user();

            $validatedData = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            // Verificar que la contraseña actual es correcta
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 401);
            }

            // Actualizar contraseña
            $user->password = Hash::make($validatedData['password']);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
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
}
