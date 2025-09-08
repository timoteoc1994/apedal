<?php

namespace App\Http\Controllers;

use App\Models\Asociacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Ciudadano;
use Illuminate\Support\Facades\Storage;
use App\Models\AuthUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ActualizarPerfilController extends Controller
{
    /**
     * Actualizar perfil sin imagen
     */
public function uploadReferentialImages(Request $request)
{
    $user = Auth::user();
    $filenames = [];
    try {
        // Validar que se reciban imágenes
        $request->validate([
            'imagen_referencial' => 'required|array',
            'imagen_referencial.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Obtener el perfil de la asociación
        $asociacion = Asociacion::find($user->profile_id);

        // Eliminar físicamente todas las imágenes anteriores
        if ($asociacion && $asociacion->imagen_referencial) {
            $imagenes_anteriores = json_decode($asociacion->imagen_referencial, true) ?? [];
            foreach ($imagenes_anteriores as $imagen) {
                $rutaCompleta = public_path('storage/' . ltrim($imagen, '/'));
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
        }

        // Subir nuevas imágenes
        if ($request->hasFile('imagen_referencial')) {
            foreach ($request->file('imagen_referencial') as $index => $image) {
                $nombreImagen = time() . '_' . $index . '_' . Auth::id() . '.' . $image->getClientOriginalExtension();
                $rutaImagen = $image->storeAs('imagen_referencial', $nombreImagen, 'public');
                $filenames[] = $rutaImagen;
            }
        }

        // Guardar solo las nuevas imágenes en la base de datos
        $asociacion->imagen_referencial = json_encode($filenames);
        $asociacion->save();

        return response()->json([
            'success' => true,
            'filenames' => $filenames,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al subir imágenes',
            'error' => $e->getMessage(),
        ], 500);
    }
}
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
                    'nickname' => 'required|string',
                    'telefono' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'ciudad' => 'required|string',
                    'referencias_ubicacion' => 'nullable|string',
                    'fecha_nacimiento' => 'required|date|before_or_equal:'.Carbon::now()->subYears(10)->toDateString(),
                    'genero' => 'required|string',
                ],[
                    'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe ser antes de '.Carbon::now()->subYears(10)->toDateString(),
                ]);

                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();

                // Acceder al perfil del ciudadano usando la relación
                $ciudadano = $user->ciudadano;

                // Actualizar los datos del ciudadano
                $ciudadano->name = $validatedData['name'];
                $ciudadano->nickname = $validatedData['nickname'];
                $ciudadano->telefono = $validatedData['telefono'];
                $ciudadano->direccion = $validatedData['direccion'];
                $ciudadano->ciudad = $validatedData['ciudad'];
                $ciudadano->referencias_ubicacion = $validatedData['referencias_ubicacion'] ?? $ciudadano->referencias_ubicacion;
                $ciudadano->fecha_nacimiento = $validatedData['fecha_nacimiento'];
                $ciudadano->genero = $validatedData['genero'];
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
                    'fecha_nacimiento' => 'required|date|before_or_equal:'.Carbon::now()->subYears(10)->toDateString(),
                    'genero' => 'required|string',
                ], [
                    'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe ser antes de '.Carbon::now()->subYears(10)->toDateString(),
                ]);
                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();
                // Acceder al perfil del reciclador usando la relación
                $reciclador = $user->reciclador;
                // Actualizar los datos del ciudadano
                $reciclador->name = $validatedData['name'];
                $reciclador->telefono = $validatedData['telefono'];
                $reciclador->fecha_nacimiento = $validatedData['fecha_nacimiento'];
                $reciclador->genero = $validatedData['genero'];
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
            } else if ($user->role == 'asociacion') {


                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'number_phone' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'city' => 'required|string',
                    'descripcion' => 'required|string',
                    'materiales_aceptados'=> 'required|array',
                    'dias_atencion' => 'required|array',
                    'hora_apertura' => 'required|string',
                    'hora_cierre' => 'required|string',
                ]);

                Log::info('Datos validados para actualizar perfil de asociación:');
                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();

                // Acceder al perfil de la asociacion
                $asociacion = $user->asociacion;

                // Actualizar los datos del asociacion
                $asociacion->name = $validatedData['name'];
                $asociacion->number_phone = $validatedData['number_phone'];
                $asociacion->direccion = $validatedData['direccion'];
                $asociacion->city = $validatedData['city'];
                $asociacion->descripcion = $validatedData['descripcion'] ?? $asociacion->descripcion;
                $asociacion->materiales_aceptados = $validatedData['materiales_aceptados'];
                $asociacion->dias_atencion = $validatedData['dias_atencion'];
                $asociacion->hora_apertura = $validatedData['hora_apertura'];
                $asociacion->hora_cierre = $validatedData['hora_cierre'];
                $asociacion->save();

                // Devolver respuesta exitosa
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente',
                    'data' => [
                        'user' => $user,
                        'profile' => $asociacion
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para actualizar el perfil'
                ], 403);
            }
        } catch (ValidationException $e) {
            Log::error('Error de validación al actualizar perfil: ' . $e->getMessage());
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
        //imprimir request
        Log::info('Request para actualizar perfil con imagen: ' . json_encode($request->all()));

        try {
            // Obtener el usuario autenticado
            $user = auth()->user();

            //actualizarperfil ciudadano
            if ($user->role === 'ciudadano') {
                // Validar los campos enviados
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'nickname' => 'required|string',
                    'telefono' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'ciudad' => 'required|string',
                    'referencias_ubicacion' => 'nullable|string',
                    'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                    'fecha_nacimiento' => 'required|date|before_or_equal:'.Carbon::now()->subYears(10)->toDateString(),
                    'genero' => 'required|string',
                ], [
                    'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe ser antes de '.Carbon::now()->subYears(10)->toDateString(),
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
                Log::info('imagen guardad');
                // Actualizar el email del usuario
                $user->email = $validatedData['email'];
                $user->save();

                // Actualizar los datos del ciudadano
                $ciudadano->name = $validatedData['name'];
                $ciudadano->nickname = $validatedData['nickname'];
                $ciudadano->telefono = $validatedData['telefono'];
                $ciudadano->direccion = $validatedData['direccion'];
                $ciudadano->ciudad = $validatedData['ciudad'];
                $ciudadano->referencias_ubicacion = $validatedData['referencias_ubicacion'] ?? $ciudadano->referencias_ubicacion;
                $ciudadano->fecha_nacimiento = $validatedData['fecha_nacimiento'];
                $ciudadano->genero = $validatedData['genero'];

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
                    'fecha_nacimiento' => 'required|date|before_or_equal:'.Carbon::now()->subYears(10)->toDateString(),
                    'genero' => 'required|string',
                ], [
                    'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento debe ser antes de '.Carbon::now()->subYears(10)->toDateString(),
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
                    $reciclador->fecha_nacimiento = $validatedData['fecha_nacimiento'];
                    $reciclador->genero = $validatedData['genero'];
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
                $validatedData = $request->validate([
                    'email' => 'required|email|unique:auth_users,email,' . $user->id,
                    'name' => 'required|string|max:255',
                    'number_phone' => 'required|string|max:15',
                    'direccion' => 'required|string',
                    'city' => 'required|string',
                    'descripcion' => 'nullable|string',
                    'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]);
                               
                // Acceder al perfil de la asociacion
                $asociacion = $user->asociacion;
                if ($request->hasFile('profile_image')) {
                    // Eliminar imagen anterior si existe
                   
                    if ($asociacion->logo_url) {
                        $oldPath = $asociacion->logo_url;
                        $oldImagePath = public_path(ltrim($oldPath, '/')); // Quitar la barra inicial si existe
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                        
                        // Generar un nombre único para la imagen
                        $imageName = 'asociacion_' . $asociacion->id . '_' . time() . '.' . $request->profile_image->extension();
                        // Guardar la imagen en el almacenamiento
                        $request->profile_image->storeAs('profiles', $imageName, 'public');
                        // Guardar SOLO la ruta relativa para la imagen (sin el dominio)
                        $relativePath = '/storage/profiles/' . $imageName;
                        // Actualizar el email del usuario
                        $user->email = $validatedData['email'];
                        $user->save();
                        // Actualizar los datos del asociacion
                        $asociacion->name = $validatedData['name'];
                        $asociacion->number_phone = $validatedData['number_phone'];
                        $asociacion->direccion = $validatedData['direccion'];
                        $asociacion->city = $validatedData['city'];
                        $asociacion->descripcion = $validatedData['descripcion'] ?? $asociacion->descripcion;
                        // Actualizar URL de la imagen si se subió una nueva
                        if (isset($relativePath)) {
                            $asociacion->logo_url = $relativePath;
                        }
                        $asociacion->save();

                         
                        // Devolver respuesta exitosa
                        return response()->json([
                            'success' => true,
                            'message' => 'Perfil actualizado correctamente con nueva imagen',
                            'data' => [
                                'user' => $user,
                                'profile' => $asociacion
                            ]
                        ]);
                    }else{
                        //ingresar la imagen ya que noe xiste es la primera vez que se sube
                        $imageName = 'asociacion_' . $asociacion->id . '_' . time() . '.' . $request->profile_image->extension();
                        // Guardar la imagen
                        $request->profile_image->storeAs('profiles', $imageName, 'public');
                        // Guardar SOLO la ruta relativa para la imagen (sin el dominio)
                        $relativePath = '/storage/profiles/' . $imageName;
                        // Actualizar el email del usuario
                        $user->email = $validatedData['email'];
                        $user->save();
                        // Actualizar los datos del asociacion
                        $asociacion->name = $validatedData['name'];
                        $asociacion->number_phone = $validatedData['number_phone'];
                        $asociacion->direccion = $validatedData['direccion'];
                        $asociacion->city = $validatedData['city'];
                        $asociacion->descripcion = $validatedData['descripcion'] ?? $asociacion->descripcion;
                        // Actualizar URL de la imagen si se subió una nueva
                        if (isset($relativePath)) {
                            $asociacion->logo_url = $relativePath;
                        }
                        $asociacion->save();
                        // Devolver respuesta exitosa
                        return response()->json([
                            'success' => true,
                            'message' => 'Perfil actualizado correctamente con nueva imagen',
                            'data' => [
                                'user' => $user,
                                'profile' => $asociacion
                            ]
                        ]);
                    }
                }
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
