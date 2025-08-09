<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\Ciudadano;
use App\Models\Reciclador;
use App\Models\SolicitudRecoleccion;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EliminarCuenta extends Controller
{
    /**
     * Eliminar cuenta del usuario autenticado
     */
    public function eliminarCuenta(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            //comentario eliinarndo con log
            Log::info('Eliminadindo cuenta del usuario: ' . $user->id);

            DB::beginTransaction();
            $auth_user = AuthUser::where('id', $user->id)->first();
            if ($auth_user->role == 'asociacion') {
                Log::info('es una asociacion');
                $asociacion = Asociacion::where('id', $auth_user->profile_id)->first();
                Log::info('eliminado a la asociacion : ' . $asociacion->name);
                if ($asociacion) {
                    // Obtener las zonas de la asociación
                    $zonas = Zona::where('asociacion_id', $asociacion->id)->get();
                    foreach ($zonas as $zona) {
                        // Obtener todas las solicitudes de recolección de la zona
                        $solicitudes = SolicitudRecoleccion::where('zona_id', $zona->id)->get();
                        foreach ($solicitudes as $solicitud) {
                            // Eliminar todas las imágenes asociadas a la solicitud
                            $imagenes = json_decode($solicitud->imagen);
                            if ($imagenes && is_array($imagenes)) {
                                foreach ($imagenes as $imagen) {
                                    $rutaCompleta = public_path('storage/' . $imagen);
                                    Log::info("Intentando eliminar archivo: {$rutaCompleta}");
                                    if (file_exists($rutaCompleta)) {
                                        unlink($rutaCompleta);
                                        Log::info("✅ Imagen eliminada correctamente: {$rutaCompleta}");
                                    } else {
                                        Log::error("❌ Imagen no encontrada: {$rutaCompleta}");
                                    }
                                }
                            }
                            // Eliminar la imagen de foto_ubicacion si existe
                            if ($solicitud->foto_ubicacion) {
                                $rutaFotoUbicacion = public_path('storage/' . $solicitud->foto_ubicacion);
                                Log::info("Intentando eliminar foto de ubicación: {$rutaFotoUbicacion}");
                                if (file_exists($rutaFotoUbicacion)) {
                                    unlink($rutaFotoUbicacion);
                                    Log::info("✅ Foto de ubicación eliminada correctamente: {$rutaFotoUbicacion}");
                                } else {
                                    Log::error("❌ Foto de ubicación no encontrada: {$rutaFotoUbicacion}");
                                }
                            }
                        }
                        // Eliminar todas las solicitudes de la zona
                        SolicitudRecoleccion::where('zona_id', $zona->id)->delete();
                    }
                    // Eliminar recicladores relacionados
                    Reciclador::where('asociacion_id', $asociacion->id)->delete();
                    //eliminar los auth_user con profile_id de los Reciclador que perteneces a esta id

                    // Eliminar zonas relacionadas
                    Zona::where('asociacion_id', $asociacion->id)->delete();
                    // Eliminar la asociación y el usuario
                    $asociacion->delete();
                    $auth_user->delete();
                    Log::info('Cuenta de la asociacion eliminada: ');
                }
            } elseif ($auth_user->role == 'reciclador') {
                $reciclador = Reciclador::where('id', $auth_user->profile_id)->first();
                if ($reciclador) {
                    // 1. Eliminar solicitudes de recolección donde el reciclador esté asignado
                    $solicitudes = SolicitudRecoleccion::where('reciclador_id', $reciclador->id)->get();
                    foreach ($solicitudes as $solicitud) {
                        // Eliminar imágenes asociadas
                        $imagenes = json_decode($solicitud->imagen);
                        if ($imagenes && is_array($imagenes)) {
                            foreach ($imagenes as $imagen) {
                                $rutaCompleta = public_path('storage/' . $imagen);
                                Log::info("Intentando eliminar archivo: {$rutaCompleta}");
                                if (file_exists($rutaCompleta)) {
                                    unlink($rutaCompleta);
                                    Log::info("✅ Imagen eliminada correctamente: {$rutaCompleta}");
                                } else {
                                    Log::error("❌ Imagen no encontrada: {$rutaCompleta}");
                                }
                            }
                        }
                        // Eliminar foto de ubicación si existe
                        if ($solicitud->foto_ubicacion) {
                            $rutaFotoUbicacion = public_path('storage/' . $solicitud->foto_ubicacion);
                            Log::info("Intentando eliminar foto de ubicación: {$rutaFotoUbicacion}");
                            if (file_exists($rutaFotoUbicacion)) {
                                unlink($rutaFotoUbicacion);
                                Log::info("✅ Foto de ubicación eliminada correctamente: {$rutaFotoUbicacion}");
                            } else {
                                Log::error("❌ Foto de ubicación no encontrada: {$rutaFotoUbicacion}");
                            }
                        }
                    }
                    // Eliminar las solicitudes de la base de datos
                    SolicitudRecoleccion::where('reciclador_id', $reciclador->id)->delete();

                    // 2. Eliminar ubicaciones asociadas al usuario
                    \App\Models\Ubicacionreciladores::where('auth_user_id', $auth_user->id)->delete();

                    // 3. Eliminar productos creados por el usuario (si aplica)
                    \App\Models\Producto::where('user_id', $auth_user->id)->delete();

                    // 4. Eliminar el usuario AuthUser
                    $auth_user->delete();

                    // 5. Eliminar el reciclador
                    $reciclador->delete();

                    Log::info('Cuenta de reciclador eliminada correctamente');
                }
            } elseif ($auth_user->role == 'ciudadano') {
                $ciudadano = Ciudadano::where('id', $auth_user->profile_id)->first();
                if ($ciudadano) {
                    // 1. Eliminar solicitudes de recolección donde el ciudadano sea el solicitante
                    $solicitudes = SolicitudRecoleccion::where('user_id', $auth_user->id)->get();
                    foreach ($solicitudes as $solicitud) {
                        // Eliminar imágenes asociadas
                        $imagenes = json_decode($solicitud->imagen);
                        if ($imagenes && is_array($imagenes)) {
                            foreach ($imagenes as $imagen) {
                                $rutaCompleta = public_path('storage/' . $imagen);
                                Log::info("Intentando eliminar archivo: {$rutaCompleta}");
                                if (file_exists($rutaCompleta)) {
                                    unlink($rutaCompleta);
                                    Log::info("✅ Imagen eliminada correctamente: {$rutaCompleta}");
                                } else {
                                    Log::error("❌ Imagen no encontrada: {$rutaCompleta}");
                                }
                            }
                        }
                        // Eliminar foto de ubicación si existe
                        if ($solicitud->foto_ubicacion) {
                            $rutaFotoUbicacion = public_path('storage/' . $solicitud->foto_ubicacion);
                            Log::info("Intentando eliminar foto de ubicación: {$rutaFotoUbicacion}");
                            if (file_exists($rutaFotoUbicacion)) {
                                unlink($rutaFotoUbicacion);
                                Log::info("✅ Foto de ubicación eliminada correctamente: {$rutaFotoUbicacion}");
                            } else {
                                Log::error("❌ Foto de ubicación no encontrada: {$rutaFotoUbicacion}");
                            }
                        }
                    }
                    // Eliminar las solicitudes de la base de datos
                    SolicitudRecoleccion::where('user_id', $auth_user->id)->delete();

                    

                    // 4. Eliminar el usuario AuthUser
                    $auth_user->delete();

                    // 5. Eliminar el ciudadano
                    $ciudadano->delete();

                    Log::info('Cuenta de ciudadano eliminada correctamente');
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta eliminada exitosamente',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar cuenta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
