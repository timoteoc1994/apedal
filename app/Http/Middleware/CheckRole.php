<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\AuthUser;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
   public function handle(Request $request, Closure $next, ...$roles): Response
{
    // Si recibe múltiples parámetros, los une
    if (count($roles) > 1) {
        $rolesString = implode(',', $roles);
    } else {
        $rolesString = $roles[0];
    }
    
    // Verificar si el usuario está autenticado
    if (!$request->user()) {
        Log::info('Usuario no autenticado intentando acceder a ruta protegida por rol: ' . implode(',', $roles));
        return response()->json(['message' => 'No autenticado'], 401);
    }

    $user = $request->user();
    $rolesArray = array_map('trim', explode(',', $rolesString)); // Usar $rolesString en lugar de $roles

        // Detectar el tipo de usuario y verificar rol según corresponda
        if ($user instanceof User) {
            // Usuario del sistema web (Spatie)
            $userRoles = $user->getRoleNames()->toArray();
            Log::info('Verificando rol de usuario (Spatie)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_roles' => $userRoles,
                'required_roles' => $rolesArray,
                'model_type' => 'User'
            ]);

            if (!$user->hasAnyRole($rolesArray)) {
                Log::info('Acceso denegado: rol incorrecto (Spatie)', [
                    'user_roles' => $userRoles,
                    'required_roles' => $rolesArray
                ]);

                return response()->json([
                    'message' => 'No autorizado para esta acción',
                    'required_roles' => $rolesArray,
                    'your_roles' => $userRoles
                ], 403);
            }

        } elseif ($user instanceof AuthUser) {
            // Usuario del sistema API (campo role)
            Log::info('Verificando rol de usuario (AuthUser)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_role' => $user->role,
                'required_roles' => $rolesArray,
                'model_type' => 'AuthUser'
            ]);

            if (!in_array($user->role, $rolesArray)) {
                Log::info('Acceso denegado: rol incorrecto (AuthUser)', [
                    'user_role' => $user->role,
                    'required_roles' => $rolesArray
                ]);

                return response()->json([
                    'message' => 'No autorizado para esta acción',
                    'required_roles' => $rolesArray,
                    'your_role' => $user->role
                ], 403);
            }
        } else {
            // Tipo de usuario no reconocido
            Log::error('Tipo de usuario no reconocido', [
                'user_class' => get_class($user),
                'user_id' => $user->id ?? 'N/A'
            ]);

            return response()->json([
                'message' => 'Error de autenticación'
            ], 500);
        }

        return $next($request);
    }
}