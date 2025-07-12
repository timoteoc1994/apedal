<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\user;
use App\Services\FirebaseService;

class CangeTiendasController extends Controller
{
    public function index(Request $request)
    {

        $resultados = [];
        $tipo = $request->input('tipo');
        $query = $request->input('query');
        //obtener id usuario autenticado
        $user = auth()->user();

        if ($tipo && $query) {
            if ($tipo === 'codigo') {

                $codigo = $query;
                $canjes = Canje::with(['authuser', 'user', 'producto'])
                    ->where('codigo', $codigo)
                    ->where('user_id', $user->id) // Asegurarse de que el canje pertenece al usuario autenticado
                    ->get();
            } else {
                $canjes = collect();
            }

            // Formatear resultados para la vista, incluyendo información del producto
            $resultados = $canjes->map(function ($canje) {
                return [
                    'id' => $canje->id,
                    'nombre' => $canje->user ? $canje->user->name : '',
                    'email' => $canje->user ? $canje->user->email : '',
                    'codigo' => $canje->codigo,
                    'puntos' => $canje->puntos_canjeados,
                    'estado' => $canje->estado,
                    'fecha_solicitado' => $canje->created_at,
                    'fecha_canjeado' => $canje->fecha_canjeado,
                    'producto' => $canje->producto ? [
                        'id' => $canje->producto->id ?? null,
                        'nombre' => $canje->producto->nombre ?? '',
                        'puntos' => $canje->producto->puntos ?? 0,
                        'descripcion' => $canje->producto->descripcion ?? '',
                        'url_imagen' => $canje->producto->url_imagen ?? null,
                    ] : null,
                ];
            });
        }

        // Retornar la vista con los resultados (se pasan como prop)
        return Inertia::render('Tienda/canjear', [
            'searchResults' => $resultados,
        ]);
    }
    function store(Request $request)
    {

        // Validar los datos de entrada
        $request->validate([
            'codigo' => 'required|string|max:255',
            'canje_id' => 'required|exists:canje,id',
        ]);



        // Actualizar el estado de canje a canjeado
        $canje = Canje::findOrFail($request->input('canje_id'));
        $canje->estado = 'canjeado'; // Cambiar el estado a canjeado
        $canje->fecha_canjeado = now(); // Registrar la fecha de canje
        $canje->save();

        //enviar notificacion al usuario
        //enviar notificacion al ciudadano
        FirebaseService::sendNotification($canje->auth_user_id, [
            'title' => 'El producto ' . $canje->producto->nombre . ' ha sido canjeado',
            'body' => 'El producto fue entregado.',
            'data' => []
        ]);
        // Redirigir hacia atras back
        return redirect()->back()->with('success', 'Canje creado exitosamente.');
    }
    function tienda_productos()
    {
        $user = auth()->user();
        $tienda = User::findOrFail($user->id);
        $productos = Producto::where('user_id', $user->id)->paginate(12);

        return inertia('Tienda/productos', [
            'tienda' => $tienda,
            'productos' => $productos
        ]);
    }
    function historial_tienda_productos()
    {
        $user = auth()->user();
        //aqui vamos a mostrar los historial de canjes realizados por la tienda
        $canjes = Canje::where('estado','canjeado')->where('user_id', $user->id)
            ->with(['producto', 'authuser.ciudadano']) // Cargar el ciudadano relacionado al authuser
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Transforma solo los items
        $canjes->getCollection()->transform(function ($canje) {
            return [
                'nombre' => $canje->authuser->ciudadano->name,
                'logo_url' => $canje->authuser->ciudadano->logo_url ?? null,
                'email' => $canje->authuser ? $canje->authuser->email : '',
                'codigo' => $canje->codigo,
                'puntos' => $canje->puntos_canjeados,
                'estado' => $canje->estado,
                'fecha_solicitado' => $canje->created_at,
                'fecha_canjeado' => $canje->fecha_canjeado,
                'producto' => $canje->producto ? [
                    'id' => $canje->producto->id ?? null,
                    'nombre' => $canje->producto->nombre ?? '',
                    'puntos' => $canje->producto->puntos ?? 0,
                    'descripcion' => $canje->producto->descripcion ?? '',
                    'url_imagen' => $canje->producto->url_imagen ?? null,
                ] : null,
            ];
        });

        return inertia('Tienda/historial', [
            'canjes' => $canjes
        ]);
    }
}
