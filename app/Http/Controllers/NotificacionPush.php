<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificacionPush extends Controller
{
    function index()
    {
        return Inertia::render('Notificacionpush/notifcaciones', []);
    }

    // Endpoint para recibir y enviar la notificación push
    public function enviar(Request $request)
    {
        $validated = $request->validate([
            'destinatario' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'route' => 'nullable|string',
            'image' => 'nullable|url',
        ]);

        // Construir el array de notificación
        $notification = [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'image' => $validated['image'] ?? null,
            'data' => [
                'route' => $validated['route'] ?? null,
            ]
        ];

        // Lógica para decidir a quién enviar según destinatario
        $success = false;
        $destinatario = $validated['destinatario'];
        if ($destinatario === 'todos') {
            //enviar a todos
            //selecconar todos los id de auth_user que tengan fcm_token
            $id_enviar = AuthUser::whereNotNull('fcm_token')->pluck('id')->toArray();
        } elseif ($destinatario === 'asociacion') {
            $id_enviar = AuthUser::where('role', 'asociacion')->whereNotNull('fcm_token')->pluck('id')->toArray();
        } elseif ($destinatario === 'reciclador') {
            $id_enviar = AuthUser::where('role', 'reciclador')->whereNotNull('fcm_token')->pluck('id')->toArray();
        } elseif ($destinatario === 'ciudadano') {
            $id_enviar = AuthUser::where('role', 'ciudadano')->whereNotNull('fcm_token')->pluck('id')->toArray();
        }
        // Enviar notificaciones si hay destinatarios
        if (!empty($id_enviar)) {
            $resultados = FirebaseService::sendMultipleNotifications($id_enviar, $notification);
            $success = in_array(true, $resultados, true);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notificación enviada correctamente.' : 'Error al enviar la notificación.'
        ]);
    }
}
