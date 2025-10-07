<?php

namespace App\Http\Controllers;

use App\Events\ActualizarPuntosCiudadano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\AuthUser;
use App\Models\Puntos;
use App\Services\FirebaseService;

class GuardarReferedo extends Controller
{
    public function guardarReferido(Request $request)  {

        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'email_referido' => 'required|email|exists:auth_users,email',
            ], [
                'email_referido.required' => 'El email del referido es obligatorio',
                'email_referido.email' => 'Debe ser un email válido',
                'email_referido.exists' => 'El email del referido no existe en el sistema',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Obtener el usuario autenticado
            $usuario = Auth::user();

            // Verificar que no se esté refiriendo a sí mismo
            if ($usuario->email === $request->email_referido) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes referirte a ti mismo'
                ], 400);
            }

            // Usar transacción para asegurar consistencia de datos
            DB::beginTransaction();

            try {
                // Verificar si ya tiene un referido asignado (con bloqueo)
                $usuarioActualizado = AuthUser::where('id', $usuario->id)
                    ->lockForUpdate()
                    ->first();

                if (!empty($usuarioActualizado->email_referido)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya tienes un referido asignado. Solo puedes tener uno.'
                    ], 400);
                }

                // Verificar que el email_referido no haya sido usado ya por otro usuario
                $emailYaReferido = AuthUser::where('email_referido', $request->email_referido)
                    ->where('id', '!=', $usuario->id)
                    ->exists();

                if ($emailYaReferido) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Este usuario ya ha sido referido por otra persona. Un usuario solo puede ser referido una vez.'
                    ], 400);
                }

                // Cargar puntos
                $puntos = Puntos::first();

                if (!$puntos) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontró la configuración de puntos'
                    ], 500);
                }

                // Buscar el usuario referido con bloqueo
                $usuarioReferido = AuthUser::where('email', $request->email_referido)
                    ->lockForUpdate()
                    ->first();

                if (!$usuarioReferido) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario referido no encontrado'
                    ], 404);
                }

                // Actualizar el email_referido del usuario usando update para atomicidad
                AuthUser::where('id', $usuario->id)
                    ->update(['email_referido' => $request->email_referido]);

                // Actualizar puntos del usuario referido de forma atómica
                AuthUser::where('id', $usuarioReferido->id)
                    ->increment('puntos', $puntos->puntos_reciclado_referido);

                // Obtener los valores actualizados
                $usuarioActualizado->refresh();
                $usuarioReferido->refresh();

                // Commit de la transacción
                DB::commit();

                // Enviar actualizaciones por WebSocket (después del commit)
                ActualizarPuntosCiudadano::dispatch($usuario->id, $usuarioActualizado->puntos);
                ActualizarPuntosCiudadano::dispatch($usuarioReferido->id, $usuarioReferido->puntos);

                // Enviar notificación push al referido
                $mensaje = "El usuario {$usuario->name} te ha referido y has ganado {$puntos->puntos_reciclado_referido} puntos.";
                FirebaseService::sendNotification($usuarioReferido->id, [
                    'title' => 'Nuevo referido',
                    'body' => $mensaje,
                    'data' => [
                        'route' => '/perfil',
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Referido guardado con éxito',
                    'data' => [
                        'email_referido' => $usuarioActualizado->email_referido,
                        'puntos_otorgados' => $puntos->puntos_reciclado_referido,
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
