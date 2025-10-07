<?php

namespace App\Http\Controllers;

use App\Events\ActualizarPuntosCiudadano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
 

            // Verificar si ya tiene un referido asignado
            if (!empty($usuario->email_referido)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes un referido asignado. Solo puedes tener uno.'
                ], 400);
            }

            // Verificar que no se esté refiriendo a sí mismo
            if ($usuario->email === $request->email_referido) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes referirte a ti mismo'
                ], 400);
            }

            // Verificar que el email_referido no haya sido usado ya por otro usuario
            $emailYaReferido = AuthUser::where('email_referido', $request->email_referido)
                ->where('id', '!=', $usuario->id)
                ->exists();

            if ($emailYaReferido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario ya ha sido referido por otra persona. Un usuario solo puede ser referido una vez.'
                ], 400);
            }

            //cargar puntos
            $puntos= Puntos::first();

            // Actualizar el email_referido del usuario
            /* $usuario->email_referido = $request->email_referido;
            $usuario->puntos += $puntos->puntos_reciclado_referido; // Asignar puntos por referido
            $usuario->save();

            //actualizar enviar por webscoket los puntos
            ActualizarPuntosCiudadano::dispatch($usuario->id, $usuario->puntos);
 */

            // Opcional: Buscar el usuario referido para obtener más información
            $usuarioReferido = AuthUser::where('email', $request->email_referido)->first();
            $usuarioReferido->puntos += $puntos->puntos_reciclado_referidor; // Asignar puntos al referido
            $usuarioReferido->save();
            ActualizarPuntosCiudadano::dispatch($usuarioReferido->id, $usuarioReferido->puntos);

            //enviar notificacion push al referido que usuario x te ha  refierido y ganaste x puntos
            $mensaje = "El usuario {$usuario->name} te ha referido y has ganado {$puntos->puntos_reciclado_referidor} puntos.";
            FirebaseService::sendNotification($usuarioReferido->id, [
                'title' => 'Nuevo referido',
                'body' => $mensaje,
                'data' => [
                    'route' => '/perfil', // Ruta a la que se dirigirá el usuario al tocar la notificación
                ]
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Referido guardado con éxito',
                'data' => [
                    'email_referido' => $usuario->email_referido,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
