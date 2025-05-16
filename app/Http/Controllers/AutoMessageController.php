<?php

namespace App\Http\Controllers;

use App\Events\EnviarMensaje;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AutoMessageController extends Controller
{
    public function index()
    {
        // Obtiene el usuario autenticado (o el primer usuario si necesitas una demo rápida)
        $user = Auth::user() ?? \App\Models\User::first();

        // Crea un mensaje automático
        $mensaje = Mensaje::create([
            'mensaje' => 'Hola desde la ruta automática - ' . now()->format('H:i:s'),
            'user_id' => $user->id,
        ]);

        // Dispara el evento de broadcast
        EnviarMensaje::dispatch($mensaje, $user);

        // Retorna una respuesta simple
        return Inertia::render('AutoMessage', [
            'status' => 'Mensaje enviado al canal de chat',
            'time' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
