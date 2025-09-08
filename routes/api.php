<?php

use App\Http\Controllers\ActualizarPerfilController;
use App\Http\Controllers\EliminarCuenta;
use App\Http\Controllers\ApiProductoController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\ImpactoAmbientalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecicladorController;
use App\Http\Controllers\AsociacionController;
use App\Http\Controllers\SolicitudRecoleccionController;
use App\Http\Controllers\MostrarSolicitudesController;
use App\Http\Controllers\UbicacionreciladoresController;
use App\Http\Controllers\SolicitudInmediataController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\CalificarReciclador;
use App\Http\Controllers\MapaAsocicacion;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MensajeController;
use App\Models\AppVersion;
use App\Http\Controllers\FormulariMensualController;
use App\Http\Controllers\GuardarReferedo;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\SoporteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/reciclador', [AuthController::class, 'solo_reciclador_register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verificar-email', [AuthController::class, 'verificarEmail']);
Route::post('/reenviar-codigo', [AuthController::class, 'reenviarCodigo']);
Route::post('/recuperar-contrasena', [AuthController::class, 'enviarCodigoRecuperacion']);
Route::post('/restablecer-contrasena', [AuthController::class, 'restablecerContrasena']);
Route::get('/ciudades_disponibles', [AuthController::class, 'getCities']);
Route::get('/asociaciones_disponibles', [AuthController::class, 'getAsociaiones']);


Route::get('tracking/{id}', [TrackingController::class, 'getLocation']);
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

//rutas control de versiones
Route::get('/version', [AppVersionController::class, 'version']);



// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mensajes/store', [MensajeController::class, 'store']);
    Route::get('/mensajes', [MensajeController::class, 'index']);
    // Rutas para todos los usuarios
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/auth/change-password', [ActualizarPerfilController::class, 'changePassword']);
    Route::post('/profile/update', [ActualizarPerfilController::class, 'update']);
    Route::post('/profile/update-with-image', [ActualizarPerfilController::class, 'updateWithImage']);
    Route::post('/profile/upload_referential_images', [ActualizarPerfilController::class, 'uploadReferentialImages']);
    //eliminar cuenta
    Route::delete('/eliminarCuenta', [EliminarCuenta::class, 'eliminarCuenta']);
    //soporte
     Route::post('/soporte', [SoporteController::class, 'index']);

    // Rutas para ciudadanos
    Route::middleware(['custom-role:ciudadano'])->prefix('ciudadano')->group(function () {
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
        //Obtener asociaciones para el mapa
        Route::get('/asociaciones', [MapaAsocicacion::class, 'getAsociaciones']);
        //obtener impacto ambiental del ciudadano
        Route::post('/impacto-ambiental', [ImpactoAmbientalController::class, 'obtenerEstadisticasPorMes']);

        //cancelar solicitud por parte del ciudadano
        Route::post('/cancelar_solicitud_ciudadano', [SolicitudRecoleccionController::class, 'cancelar_solicitud_ciudadano']);
        //actualizar puntos del ciudadano manual
        Route::post('/actualizar-puntos-ciudadano', [SolicitudRecoleccionController::class, 'actualizarPuntosCiudadano']);

        //ruta traer productos de tienda
        Route::get('/productos', [ApiProductoController::class, 'index']);
        //ruta para canjear un producto
        Route::post('/canjear-producto', [ApiProductoController::class, 'canjearProducto']);
        Route::get('/historial-canjes', [ApiProductoController::class, 'historialCanjes']);

        //ruta para ranking de ciudadanos que mas han reciclado
        Route::get('/top-recicladores', [RankingController::class, 'index']);

        //guardar refereido
        Route::post('/guardar-referido', [GuardarReferedo::class, 'guardarReferido']);
    });


    // Rutas para recicladores
    Route::middleware(['custom-role:reciclador'])->prefix('reciclador')->group(function () {
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

        //actualizar solo un material de la asignacion
        Route::put('/asignaciones/{id}/editar-materiales', [RecicladorController::class, 'actualizarRevisionMaterialesunico']);

        //ruta para ver los pendientes del reciclador 
        Route::get('/reciclador/solicitudes/pendientes', [RecicladorController::class, 'Pendientes']);
        //ruta para contar las solicitudes pendientes de un reciclador
        Route::get('reciclador/solicitudes/pendientes/contador', [RecicladorController::class, 'obtenerContadorPendientes']);
        //verhistorial de las solicitudes de un reciclador
        Route::get('/reciclador/solicitudes/historial', [RecicladorController::class, 'Historial']);

        //ver detalles de una solcitud de un reciclador
        Route::get('/reciclador/solicitudes/detalles/{id}', [RecicladorController::class, 'show']);

        // Ruta para que el reciclador actualice su ubicación
        Route::post('/reciclador/update-location', [UbicacionreciladoresController::class, 'updateLocation']);
        //consultar ubicacion actual
        Route::get('/consultar/ubicacion/reciclador', [UbicacionreciladoresController::class, 'ubiacacionActual']);

        // ⬇️ AGREGAR ESTAS 3 LÍNEAS NUEVAS ⬇️
        Route::get('/sincronizar-asignaciones', [RecicladorController::class, 'sincronizarAsignaciones']);
        Route::post('/solicitudes/{id}/marcar-vista', [RecicladorController::class, 'marcarSolicitudVista']);
        Route::get('/info-sincronizacion', [RecicladorController::class, 'obtenerInfoSincronizacion']);

        //obtener estadisticas para el perfil
        Route::get('/estadisticas/perfil', [RecicladorController::class, 'obtenerEstadisticas']);

        //cancelar la solcitud por parte del reciclador
        Route::post('/cancelar_solicitud_reciclador', [RecicladorController::class, 'cancelar_solicitud_reciclador']);
    });


    // Rutas para asociaciones
    Route::middleware(['custom-role:asociacion'])->prefix('asociacion')->group(function () {
        // Obtener todas las solicitudes de mi asocion con estado pendiente
        Route::get('/asociacion/mostrar/solicitudes', [MostrarSolicitudesController::class, 'index']);
        //obtener detalles de una solicitud de mi asociacion
        Route::get('/mostrar/asociaciones/detalles/{id}', [MostrarSolicitudesController::class, 'show']);
        //registrar un reciclador
        Route::post('/asociacion/registrar/reciclador', [MostrarSolicitudesController::class, 'crearReciclador']);
        //obtener perfil de un reciclador
        Route::get('/asociacion/reciclador/{id}', [MostrarSolicitudesController::class, 'getReciclador']);
        //asignar un reciclador a una solcitud
        Route::post('/solicitudes/asignar-reciclador', [MostrarSolicitudesController::class, 'asignarReciclador']);

        // Obtener recicladores disponibles para asignar
        Route::get('/recicladores/disponibles', [MostrarSolicitudesController::class, 'obtenerRecicladoresDisponibles']);

        //Obtener cuantos solcitiudes y recicladores tiene mi asociacion
        Route::get('/estadisticas/sr', [MostrarSolicitudesController::class, 'getEstadisticas']);
        //obtener recicladores por asociacion
        Route::get('/recicladores', [AsociacionController::class, 'getRecicladores']);
        //obtener recicladores por asociacion nuevos
        Route::get('/recicladores/nuevos', [AsociacionController::class, 'getRecicladoresnuevos']);

        //actualizar el perfil para un reciclador
        Route::put('/recicladores/actualizar/{id}', [AsociacionController::class, 'updateReciclador']);
        //activar cuenta del reciclador
        Route::put('/recicladores/activarcuenta/{id}', [AsociacionController::class, 'activarcuentaReciclador']);

        //obtener estadisticas para el perfil de recicaldores
        Route::get('/reciclador_estadisticas', [AsociacionController::class, 'obtenerEstadisticas']);

        //registrar formulario mensual
         Route::get('/formulario-mensual/index', [FormulariMensualController::class, 'index']);
        Route::post('/formulario-mensual', [FormulariMensualController::class, 'store']);
        Route::put('/formulario-mensual/{id}', [FormulariMensualController::class, 'update']);
        Route::get('/formulario-mensual/{id}', [FormulariMensualController::class, 'show']);
        Route::delete('/formulario-mensual/{id}', [FormulariMensualController::class, 'destroy']);

        Route::post('/recicladores', [AuthController::class, 'registerRecycler']);
    });
});

Route::get('/tracking/{id}', [TrackingController::class, 'getLocationData']);
