<?php

use App\Http\Controllers\AsociacionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ViewAsociationController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ZonaController;
use App\Events\EnviarMensaje;
use App\Http\Controllers\AutoMessageController;
use App\Http\Controllers\Prueba;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\TrackingController;
// Ruta para autenticación de canales (debe estar en web.php, no en api.php)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/test-broadcast', function () {
    broadcast(new EnviarMensaje("Hola desde Laravel!"));
    return "Evento enviado";
});
Route::get('/emitir', function () {
    broadcast(new EnviarMensaje('Mensaje de prueba desde /emitir'));
    return 'Evento emitido';
});
//disparar vento para actualizar cambios en la solcitudes
Route::get('/cambio', [Prueba::class, 'index']);

Route::middleware(['auth'])->group(function () {
    Route::get('/asociation', [ViewAsociationController::class, 'index'])->name('asociation.index');
    Route::get('/asociation/show/{id}', [ViewAsociationController::class, 'show'])->name('asociation.index.show');
    Route::patch('/asociation/information', [ViewAsociationController::class, 'update'])->name('asociation.index.update');
    Route::delete('/asociation_delete/{deudas}', [ViewAsociationController::class, 'delete'])->name('asociation.index.delete');

    //crud ciudades
    Route::get('/ciudad', [CityController::class, 'index'])->name('ciudad.index');
    Route::post('/ciudad/nuevo', [CityController::class, 'store'])->name('ciudad.index.nuevo');
    Route::delete('/ciudad_delete/{deudas}', [CityController::class, 'delete'])->name('ciudad.index.delete');
    Route::get('/cities', [CityController::class, 'getCities']);

    // Rutas para las zonas
    Route::get('/zonas/mapa', [ZonaController::class, 'mapa'])->name('zonas.mapa');
    Route::get('/zonas/{id}/obtener', [ZonaController::class, 'obtener'])->name('zonas.obtener');
    Route::post('/zonas', [ZonaController::class, 'store'])->name('zonas.store');
    Route::put('/zonas/{id}', [ZonaController::class, 'update'])->name('zonas.update');
    Route::delete('/zonas/{id}', [ZonaController::class, 'destroy'])->name('zonas.destroy');
    Route::get('/api/zonas', [ZonaController::class, 'obtenerZonas'])->name('api.zonas');

    //para las asociasiones
    Route::post('/asociations', [AsociacionController::class, 'store'])->name('asociation.store');
    Route::delete('/asociacion/{id}', [AsociacionController::class, 'eliminarAsociacion'])->name('asociation.index.delete');
});

Route::get('/auto-message', [AutoMessageController::class, 'index']);

Route::get('/test-solicitud-evento', function () {
    // Crear datos de prueba similares a tu solicitud
    $solicitudPrueba = [
        'id' => 999,
        'direccion' => 'Dirección de prueba',
        'referencia' => 'Cerca del parque',
        'estado' => 'buscando_reciclador',
        'peso_total' => 10,
        'latitud' => -1.23456,
        'longitud' => -78.98765,
        'created_at' => now(),
        'user' => [
            'id' => 1,
            'name' => 'Usuario Prueba',
            'ciudadano' => [
                'telefono' => '0999999999'
            ]
        ],
        'materiales' => [
            ['tipo' => 'Plástico', 'peso' => 5],
            ['tipo' => 'Cartón', 'peso' => 5]
        ]
    ];

    $recicladorId = 3; // ID del reciclador que estás probando

    // Disparar el evento
    \App\Events\NuevaSolicitudInmediata::dispatch((object)$solicitudPrueba, $recicladorId);

    return response()->json([
        'message' => 'Evento de prueba disparado',
        'canal' => 'reciclador-' . $recicladorId
    ]);
});

// routes/web.php
Route::get('/test-redis', function () {
    try {
        // Test conexión
        Redis::ping();

        // Guardar dato
        Redis::set('saludo', 'Hola desde Laravel y Redis!');

        // Leer dato
        $valor = Redis::get('saludo');

        return "Redis funcionando! Valor: " . $valor;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/tracking/{id?}', [TrackingController::class, 'show'])->name('tracking.show');

Route::get('/test-redis-save/{id?}', function ($id = 3) {
    // Intentar guardar datos de prueba
    $testData = [
        'auth_user_id' => $id,
        'latitude' => -1.2491,
        'longitude' => -78.6167,
        'timestamp' => now()->toIso8601String(),
        'status' => 'disponible',
        'updated_at' => now()->toIso8601String(),
    ];

    try {
        // Guardar en Redis
        $key = "recycler:location:{$id}";
        Redis::setex($key, 300, json_encode($testData));

        // Verificar que se guardó
        $saved = Redis::get($key);

        return response()->json([
            'message' => 'Prueba de Redis',
            'key' => $key,
            'data_enviado' => $testData,
            'data_guardado' => json_decode($saved, true),
            'redis_activo' => Redis::ping(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
