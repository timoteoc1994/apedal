<?php

namespace App\Http\Controllers;

use App\Events\ActualizarPuntosCiudadano;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Canje;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ApiProductoController extends Controller
{
    function index(Request $request)
    {
        // Obtener todas las marcas participantes con sus productos
        $marcas = User::with('productos')->whereHas('roles', function($query) {
            $query->where('name', 'Tienda');
        })->get();
        
        
        return response()->json($marcas);
    }
    function canjearProducto(Request $request)
    {
     
        /** @var \App\Models\AuthUser $authuser */
        $authuser = Auth::user();
        $productoId = $request->producto_id;
        $cantidad = $request->cantidad;

        // Validar que el producto existe y pertenece a una tienda
        $producto = Producto::where('id', $productoId)
            ->whereHas('user', function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'Tienda');
                });
            })->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado o no pertenece a una tienda.'], 404);
        }

        
        // Verificar si el usuario tiene suficientes puntos
        if ($authuser->puntos < ($producto->puntos * $cantidad)) {
            return response()->json(['error' => 'No tienes suficientes puntos para canjear este producto.'], 400);
        }



        // Crear el canje
        Canje::create([
            'auth_user_id' => $authuser->id,
            'user_id' => $producto->user_id, // ID de la tienda que ofrece el producto
            'producto_id' => $productoId,
            'puntos_canjeados' => $producto->puntos * $cantidad,
            'codigo' => 'ADRI-' . strtoupper(uniqid()), // Generar un código único
        ]);


        // Actualizar los puntos del usuario
        $authuser->puntos -= ($producto->puntos * $cantidad);
        $authuser->save();

        //enviar actualizacion de puntos
        // Disparar el evento
        event(new ActualizarPuntosCiudadano($authuser->id, $authuser->puntos));

        //disparar una notificacion al usuario
        //enviar notificacion al ciudadano
        FirebaseService::sendNotification($authuser->id, [
            'title' => 'Canje de producto exitoso',
            'body' => 'Has canjeado un producto exitosamente.',
            'data' => [

            ]
        ]);

        return response()->json(['message' => 'Producto canjeado exitosamente, puedes revisarlo en tu historial.']);
    }
    function historialCanjes(Request $request)
    {
     
        /** @var \App\Models\AuthUser $authuser */
        $authuser = Auth::user();
        $historial = Canje::with('producto','user')->where('auth_user_id', $authuser->id)->get();

        return response()->json($historial);
    }
}