<?php

use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\AsociacionController;
use App\Http\Controllers\CangeTiendasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstadisticaControlador;
use App\Http\Controllers\MatrizRecuperacionController;
use App\Http\Controllers\NotificacionPush;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PuntosController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ViewAsociationController;
use App\Http\Controllers\ZonaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

//sacar a la ruta /dasboard para poner con roles Adminsitrador y Tienda cuando se autentique


Route::middleware(['auth', 'role:Administrador,Tienda,Moderador'])->group(function () {





    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:Administrador,Moderador'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/ciudad', [CityController::class, 'index'])->name('ciudad.index');

    //ruta ciudades
    Route::get('/ciudad', [CityController::class, 'index'])->name('ciudad.index');
    Route::post('/ciudad/nuevo', [CityController::class, 'store'])->name('ciudad.index.nuevo');
    Route::delete('/ciudad_delete/{deudas}', [CityController::class, 'delete'])->name('ciudad.index.delete');
    Route::get('/cities', [CityController::class, 'getCities']);

    // Rutas para las zonas
    Route::get('/zonas/index', [ZonaController::class, 'index'])->name('zonas.index');
    Route::get('/zonas/mapa/{ciudad}', [ZonaController::class, 'mapa'])->name('zonas.mapa');
    Route::get('/zonas/{id}/obtener', [ZonaController::class, 'obtener'])->name('zonas.obtener');
    Route::post('/zonas', [ZonaController::class, 'store'])->name('zonas.store');
    Route::put('/zonas/{id}', [ZonaController::class, 'update'])->name('zonas.update');
    Route::delete('/zonas/{id}', [ZonaController::class, 'destroy'])->name('zonas.destroy');
    Route::get('/api/zonas', [ZonaController::class, 'obtenerZonas'])->name('api.zonas');

    //para las asociasiones
    Route::get('/asociation', [ViewAsociationController::class, 'index'])->name('asociation.index');
    Route::get('/asociation/perfil/{id}', [ViewAsociationController::class, 'perfil'])->name('asociation.index.perfil');
    Route::get('/asociation/{id}/recicladores', [ViewAsociationController::class, 'recicladores'])->name('asociation.index.recicladores');
    Route::get('/asociation/{id}/recicladoresperfil', [ViewAsociationController::class, 'recicladoresperfil'])->name('asociation.index.recicladoresperfil');
    Route::get('/asociation/show/{id}', [ViewAsociationController::class, 'show'])->name('asociation.index.show');
    Route::get('/asociation/show/recicladores/{id}', [ViewAsociationController::class, 'showrecicladores'])->name('asociation.index.show.recicladores');
    Route::patch('/asociation/information', [ViewAsociationController::class, 'update'])->name('asociation.index.update');
    Route::patch('/asociation/information/recicladores', [ViewAsociationController::class, 'updaterecicladores'])->name('asociation.index.update.recicladores');
    Route::delete('/asociation_delete/{deudas}', [ViewAsociationController::class, 'delete'])->name('asociation.index.delete');
    Route::delete('/asociation_delete/reciclador/{deudas}', [ViewAsociationController::class, 'deletereciclador'])->name('asociation.index.reciclador.delete');
    Route::get('/tracking/{id?}', [TrackingController::class, 'show'])->name('tracking.show');


    // Ruta para mostrar la lista de ciudadanos
    Route::get('/ciudadanos', [CiudadanoController::class, 'index'])->name('ciudadano.index');
    // Ruta para mostrar perfil de ciudadano
    Route::get('/ciudadanos/{id}', [CiudadanoController::class, 'perfil_ciudadano'])->name('ciudadano.perfil');
    // Ruta para generar PDF del ciudadano
    Route::get('/ciudadanos/{id}/reporte-pdf', [CiudadanoController::class, 'generarReportePDF'])->name('ciudadano.generar_reporte_pdf');
    // Ruta para eliminar un ciudadano
    Route::delete('/ciudadanos/{id}', [CiudadanoController::class, 'deleteCiudadano'])->name('ciudadano.delete');
    // Ruta para mostrar el formulario de edición
    // Ruta para mostrar el formulario de edición de ciudadano
    Route::get('/ciudadanos/{id}/editar', [CiudadanoController::class, 'edit'])->name('ciudadano.edit');

    // Ruta para procesar la actualización de ciudadano
    Route::put('/ciudadanos/{id}', [CiudadanoController::class, 'update'])->name('ciudadano.update');

    //crud para version
    Route::get('/versiones', [AppVersionController::class, 'index'])->name('versions.index');
    Route::put('/versiones/{appVersion}', [AppVersionController::class, 'update'])->name('versions.update');
    Route::post('/versiones', [AppVersionController::class, 'store'])->name('versions.store');

    Route::get('/crear-ciudadano', [CiudadanoController::class, 'create'])->name('ciudadano.create');

    //rutas puntos
    Route::get('/puntos', [PuntosController::class, 'edit'])->name('puntos.edit');
    Route::put('/puntos', [PuntosController::class, 'update'])->name('puntos.update');
    Route::post('/puntos', [PuntosController::class, 'store'])->name('puntos.store');

    //Notificacion push
    Route::get('/notificacionpush', [NotificacionPush::class, 'index'])->name('notificacionpush.create');
    Route::post('/notificaciones/enviar', [NotificacionPush::class, 'enviar'])->name('notificacionpush.enviar');

    //tienda
    Route::get('/productos', [ProductoController::class, 'index'])->name('producto.index');
    Route::post('/productos/nuevo', [ProductoController::class, 'store'])->name('producto.index.nuevo');
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('producto.index.eliminar');
    Route::put('/productos/{id}/editar', [ProductoController::class, 'update'])->name('producto.update');

    //rutas productos
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('producto.index.show');
    Route::post('/productos/{id}/guardar', [ProductoController::class, 'guardar_tiendas'])->name('producto.index.imagen.tiendas');
    Route::delete('/productos/tienda/{id}', [ProductoController::class, 'destroytienda'])->name('producto.index.tienda.eliminar');
    Route::put('/productos/tienda/actualizar/{id}', [ProductoController::class, 'actualizartienda'])->name('producto.index.tienda.actualizar');

    //matriz de recuperacion
    Route::get('/matrizrecuperacion', [MatrizRecuperacionController::class, 'index'])->name('matrizrecuperacion.index');

     Route::get('descargar_excel', [MatrizRecuperacionController::class, 'descargar_excel'])->name('descargar_excel');

     //Estadisticas solicitudes
     Route::get('/estadisticasolicitudes', [EstadisticaControlador::class, 'index'])->name('estadisticasolicitudes.index');

     Route::post('descargar_pdf_estadisticas', [EstadisticaControlador::class, 'descargar_pdf_estadisticas'])->name('descargar_pdf_estadisticas');

    // Obtener asociaciones filtradas por ciudad(s)
    Route::get('/estadistica/asociaciones', [EstadisticaControlador::class, 'asociacionesPorCiudad'])->name('estadistica.asociaciones');

     //Ayuda y soporte
        Route::get('/ayuda_soporte', [SoporteController::class, 'indexweb'])->name('ayuda_soporte.index');
    // cambiar estado de un soporte
    Route::patch('/ayuda_soporte/{soporte}/estado', [SoporteController::class, 'updateEstado'])->name('ayuda_soporte.update.estado');

});

Route::middleware(['auth', 'role:Tienda'])->group(function () {
    Route::get('/canjear', [CangeTiendasController::class, 'index'])->name('canje.index');
    Route::post('/canjear', [CangeTiendasController::class, 'store'])->name('canje.store');
    Route::get('/tienda/productos', [CangeTiendasController::class, 'tienda_productos'])->name('tienda.productos');
    Route::get('historial/tienda/productos', [CangeTiendasController::class, 'historial_tienda_productos'])->name('tienda.productos.historial');
});


Route::middleware(['auth', 'role:Administrador'])->group(function () {

    //rutas usuarios
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
});

Route::get('/privacidad', function () {
    return Inertia::render('Privacidad');
})->name('privacidad');
Route::get('/soporte', function () {
    return Inertia::render('Soporte');
})->name('soporte');
require __DIR__ . '/auth.php';
