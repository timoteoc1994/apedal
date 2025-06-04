<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mensaje;
use App\Events\EnviarMensaje;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MensajeController extends Controller
{
    public function store(Request $request)
    {

        try {
            $request->validate([
                'mensaje' => 'required|string'
            ]);

            $user = Auth::user();


            $mensaje = Mensaje::create([
                'mensaje' => $request->mensaje,
                'user_id' => $user->id,
                'solicitud_id' => $request->solicitud_id
            ]);

            broadcast(new EnviarMensaje($mensaje, $user))->toOthers();

            return response()->json([
                'message' => 'Mensaje enviado correctamente',
                'data' => $mensaje
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            Log::info('Solicitando mensajes...');

            Log::info('Obteniendo solicitud_id de la solicitud...');

            $solicitudId = request()->query('solicitud_id');

            if (!$solicitudId) {
                return response()->json(['error' => 'El parámetro solicitud_id es requerido'], 400);
            }

            // ✅ Obtener solo los mensajes con el solicitud_id solicitado
            $mensajes = Mensaje::with('user')
                ->where('solicitud_id', $solicitudId)
                ->latest()
                ->get();

            Log::info("Mensajes encontrados para solicitud_id $solicitudId: " . $mensajes->count());

            $data = [];
            foreach ($mensajes as $mensaje) {
                $profileName = $this->getProfileName($mensaje->user);

                $data[] = [
                    'id' => $mensaje->id,
                    'texto' => $mensaje->mensaje,
                    'usuario' => $profileName,
                    'tiempo' => $mensaje->created_at->toDateTimeString(),
                    'user_id' => $mensaje->user_id
                ];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error al cargar mensajes: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Error al cargar mensajes',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    private function getProfileName($user)
    {
        if (!$user) {
            return 'Usuario Desconocido';
        }

        try {
            Log::info('Obteniendo nombre para usuario:', [
                'user_id' => $user->id,
                'role' => $user->role,
                'profile_id' => $user->profile_id
            ]);

            switch ($user->role) {
                case 'ciudadano':
                    $ciudadano = $user->ciudadano;
                    if ($ciudadano) {
                        return $ciudadano->name ?? 'Ciudadano';
                    }
                    break;

                case 'reciclador':
                    $reciclador = $user->reciclador;
                    if ($reciclador) {
                        return $reciclador->name ?? 'Reciclador';
                    }
                    break;

                case 'asociacion':
                    $asociacion = $user->asociacion;
                    if ($asociacion) {
                        return $asociacion->name ?? 'Asociación'; // Ajusta según tu modelo Asociacion
                    }
                    break;
            }

            // Si no se encuentra el perfil específico, devolver un nombre genérico
            return 'Usuario ' . ucfirst($user->role);
        } catch (\Exception $e) {
            Log::error('Error obteniendo nombre de perfil: ' . $e->getMessage());
            Log::error('Usuario data: ' . json_encode($user->toArray()));
            return 'Usuario';
        }
    }
}
