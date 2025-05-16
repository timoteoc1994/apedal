<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verificar si el usuario está autenticado
        if (!$request->user()) {
            Log::info('Usuario no autenticado intentando acceder a ruta protegida por rol: ' . $role);
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Debug para verificar el rol del usuario
        Log::info('Verificando rol de usuario', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'user_role' => $request->user()->role,
            'required_role' => $role
        ]);

        // Verificar si el usuario tiene el rol requerido
        if ($request->user()->role !== $role) {
            Log::info('Acceso denegado: rol incorrecto', [
                'user_role' => $request->user()->role,
                'required_role' => $role
            ]);

            return response()->json([
                'message' => 'No autorizado para esta acción',
                'required_role' => $role,
                'your_role' => $request->user()->role
            ], 403);
        }

        return $next($request);
    }
}
