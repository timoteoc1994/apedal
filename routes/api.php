<?php

use App\Http\Controllers\ActualizarPerfilController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\RecicladorController;
use App\Http\Controllers\AsociacionController;
use App\Http\Controllers\SolicitudRecoleccionController;
use App\Http\Controllers\MostrarSolicitudesController;
use App\Http\Controllers\UbicacionreciladoresController;
use App\Http\Controllers\SolicitudInmediataController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\CalificarReciclador;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/mensajes', function () {
    return \App\Models\Mensaje::with('user')->latest()->take(50)->get();
});

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ciudades_disponibles', [AuthController::class, 'getCities']);



Route::post('/direct-notification', function (Request $request) {
    try {
        $validated = $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(storage_path('app/appedal-ffe02-firebase-adminsdk-fbsvc-98fe6577e7.json'));

        $messaging = $factory->createMessaging();

        $notification = \Kreait\Firebase\Messaging\Notification::create(
            $validated['title'],
            $validated['body']
        );

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $validated['token'])
            ->withNotification($notification)
            ->withData($validated['data'] ?? []);

        $result = $messaging->send($message);

        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ]);
    }
});

// En routes/api.php
Route::get('/test-notification/{token}', function ($token) {
    try {
        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(storage_path('app/appedal-ffe02-firebase-adminsdk-fbsvc-98fe6577e7.json'));

        $messaging = $factory->createMessaging();

        $notification = \Kreait\Firebase\Messaging\Notification::create(
            'Prueba desde endpoint directo',
            'Este es un mensaje de prueba directo'
        );

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData(['tipo' => 'prueba_endpoint', 'timestamp' => time()]);

        $result = $messaging->send($message);

        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::middleware('auth:sanctum')->post('/update-fcm-token', function (Request $request) {
    $request->validate([
        'fcm_token' => 'required|string',
    ]);

    $user = $request->user();
    $user->fcm_token = $request->fcm_token;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Token FCM actualizado correctamente'
    ]);
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Rutas para todos los usuarios
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/auth/change-password', [ActualizarPerfilController::class, 'changePassword']);
    Route::post('/profile/update', [ActualizarPerfilController::class, 'update']);
    Route::post('/profile/update-with-image', [ActualizarPerfilController::class, 'updateWithImage']);
    // Rutas para ciudadanos
    Route::middleware(['role:ciudadano'])->prefix('ciudadano')->group(function () {



        //ruta guardar solicitudes agendadas
        Route::post('/ciudadanos/solicitudes', [SolicitudRecoleccionController::class, 'store']);
        //ruta obtener solicutudes por fecha
        Route::get('/ciudadanos/solicitudes/historial', [SolicitudRecoleccionController::class, 'index']);
        // Obtener detalles de una solicitud
        Route::get('/ciudadanos/solicitudes/detalles/{id}', [SolicitudRecoleccionController::class, 'show']);
        // Cancelar una solicitud
        Route::delete('/ciudadanos/solicitudes/cancelar/{id}', [SolicitudRecoleccionController::class, 'destroy']);


        // Ruta para solicitudes inmediatas
        Route::post('ciudadanos/solicitudes/inmediata', [SolicitudInmediataController::class, 'buscarRecicladores']);
        // Ruta para que el ciudadano verifique el estado de su solicitud inmediata
        Route::get('ciudadanos/solicitudes/inmediata/{id}/estado', [SolicitudInmediataController::class, 'verificarEstado']);

        //ruta para obtener el detalle de uns solicitud cuando sea aceptada
        Route::get('/ciudadanos/solicitudes/recoleccion/{id}', [SolicitudRecoleccionController::class, 'obtenerDetallesRecoleccion']);

        //calificar al reciclador
        Route::post('/calificar-recolector', [CalificarReciclador::class, 'calificarReciclador']);
    });


    // Rutas para recicladores
    Route::middleware(['role:reciclador'])->prefix('reciclador')->group(function () {
        Route::get('/asignaciones', [RecicladorController::class, 'getAsignaciones']);
        Route::put('/asignaciones/{id}/actualizar', [RecicladorController::class, 'updateAsignacion']);
        //actualizar estado de un reciclador
        Route::get('/estado', [RecicladorController::class, 'getEstado']);
        Route::put('/actualizar/estado', [RecicladorController::class, 'updateStatus']);
        Route::get('/validar-cambio-estado', [RecicladorController::class, 'validarCambioEstadoRequest']);

        Route::get('/asignaciones', [RecicladorController::class, 'obtenerAsignaciones']);

        //obtener asignaciones por id de usuario
        Route::get('/asignacionespor/{id}', [RecicladorController::class, 'obtenerAsignacionPorId']);
        Route::get('/asignaciones/{id}', [RecicladorController::class, 'obtenerDetalleAsignacion']);
        Route::put('/asignaciones/{id}/estado', [RecicladorController::class, 'actualizarEstado']);

        //ruta para actualizar la revision de los materiales y calificar al ciudadano
        Route::put('/asignaciones/{id}/revision', [RecicladorController::class, 'actualizarRevisionMateriales']);

        //ruta para ver los pendientes del reciclador 
        Route::get('/reciclador/solicitudes/pendientes', [RecicladorController::class, 'Pendientes']);
        //verhistorial de las solicitudes de un reciclador
        Route::get('/reciclador/solicitudes/historial', [RecicladorController::class, 'Historial']);

        //ver detalles de una solcitud de un reciclador
        Route::get('/reciclador/solicitudes/detalles/{id}', [RecicladorController::class, 'show']);

        // Ruta para que el reciclador actualice su ubicación
        Route::post('/reciclador/update-location', [UbicacionreciladoresController::class, 'updateLocation']);
    });

    // Rutas para asociaciones
    Route::middleware(['role:asociacion'])->prefix('asociacion')->group(function () {
        // Obtener todas las solicitudes de mi asocion con estado pendiente
        Route::get('/asociacion/mostrar/solicitudes', [MostrarSolicitudesController::class, 'index']);
        //obtener detalles de una solicitud de mi asociacion
        Route::get('/mostrar/asociaciones/detalles/{id}', [MostrarSolicitudesController::class, 'show']);
        //registrar un reciclador
        Route::post('/asociacion/registrar/reciclador', [MostrarSolicitudesController::class, 'crearReciclador']);
        //obtener perfil de un reciclador
        Route::get('/asociacion/reciclador/{id}', [MostrarSolicitudesController::class, 'getReciclador']);

        Route::post('/solicitudes/asignar-reciclador', [MostrarSolicitudesController::class, 'asignarReciclador']);

        // Obtener recicladores disponibles para asignar
        Route::get('/recicladores/disponibles', [MostrarSolicitudesController::class, 'obtenerRecicladoresDisponibles']);

        Route::get('/recicladores', [AsociacionController::class, 'getRecicladores']);
        Route::post('/recicladores', [AuthController::class, 'registerRecycler']);
        Route::get('/solicitudes', [AsociacionController::class, 'getSolicitudes']);
        Route::post('/asignar', [AsociacionController::class, 'asignarSolicitud']);
    });
});

Route::get('/tracking/{id}', [TrackingController::class, 'getLocationData']);
