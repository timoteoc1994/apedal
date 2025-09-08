<?php

namespace App\Http\Controllers;

use App\Models\Soporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class SoporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info($request->all());
        
          try {
            $request->validate([
                'mensaje' => 'required|string'
            ]);

            $user = Auth::user();
            if(!$user){
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $mensaje = Soporte::create([
                'mensaje' => $request->mensaje,
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Mensaje enviado correctamente',
                'data' => $mensaje
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //rutas web

public function indexweb(Request $request)
{
    $soportes = Soporte::with([
        'auth_user:id,email,role,profile_id',
        'auth_user.reciclador:id,name,telefono',
        'auth_user.ciudadano:id,name,nickname',
        'auth_user.asociacion:id,name,city',
    ])->orderByDesc('created_at')->paginate(10);

    // Transformar para exponer un único profile según role
    $soportes->getCollection()->transform(function($s) {
        $user = $s->auth_user;
        $profile = null;

        if ($user) {
            if ($user->role === 'reciclador' && $user->reciclador) {
                $profile = $user->reciclador->toArray();
            } elseif ($user->role === 'ciudadano' && $user->ciudadano) {
                $profile = $user->ciudadano->toArray();
            } elseif ($user->role === 'asociacion' && $user->asociacion) {
                $profile = $user->asociacion->toArray();
            }

            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'profile' => $profile, // null si no existe el perfil
            ];
        } else {
            $userData = null;
        }

        $item = $s->toArray();
        $item['auth_user'] = $userData;
        return $item;
    });

    return Inertia::render('Soporte/Index', [
        'soportes' => $soportes
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Cambiar el estado de un soporte (resuelto, contestado, no_encontrado)
     */
    public function updateEstado(Request $request, Soporte $soporte)
    {
        $validStates = ['pendiente', 'resuelto', 'contestado', 'no_encontrado'];

        $data = $request->validate([
            'estado' => ['required', 'string'],
        ]);

        if (!in_array($data['estado'], $validStates)) {
            return response()->json(['message' => 'Estado no válido'], 422);
        }

        try {
            $soporte->estado = $data['estado'];
            $soporte->save();

            // Si la petición viene por Inertia (web) redirigimos con flash
           return back()->with('success', 'Estado actualizado');

            return redirect()->route('ayuda_soporte.index')->with('success', 'Estado actualizado');
        } catch (\Exception $e) {
            Log::error('Error actualizando estado soporte: ' . $e->getMessage());
            return response()->json(['message' => 'Error actualizando estado'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Soporte $soporte)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Soporte $soporte)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Soporte $soporte)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Soporte $soporte)
    {
        //
    }
}
