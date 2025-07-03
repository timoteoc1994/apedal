<?php

use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\AsociacionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\NotificacionPush;
use App\Http\Controllers\PuntosController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/about', fn() => Inertia::render('About'))->name('about');

    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    //ruta ciudades
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

    // Ruta para mostrar la lista de ciudadanos
    Route::get('/ciudadanos', [CiudadanoController::class, 'index'])->name('ciudadano.index');
    // Ruta para mostrar perfil de ciudadano
    Route::get('/ciudadanos/{id}', [CiudadanoController::class, 'perfil_ciudadano'])->name('ciudadano.perfil');
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
});

Route::get('/tracking/{id?}', [TrackingController::class, 'show'])->name('tracking.show');


require __DIR__ . '/auth.php';
