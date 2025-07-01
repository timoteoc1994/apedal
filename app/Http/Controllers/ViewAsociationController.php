<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\City;
use App\Models\Reciclador;


class ViewAsociationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $asociations = AuthUser::where('role', 'asociacion')
            ->when($search, function ($query, $search) {
                $query->where('email', 'LIKE', "%{$search}%")
                    ->orWhereHas('asociacion', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            })
            ->with('asociacion') // Cargar la relación
            ->paginate(10);
        
        //anadir una nueva variable numero de reciclador a $asociations
       $numero_recicladores=0;
        foreach ($asociations as $asociation) {
            $numero_recicladores = Reciclador::where('asociacion_id',$asociation->profile_id)
                ->where('asociacion_id', $asociation->id)
                ->count();
            $asociation->numero_recicladores = $numero_recicladores;
        }

        return Inertia::render('asociation/index', [
            'Asociations' => $asociations,
            'filters' => $request->only(['search'])
        ]);
    }
    public function show(Request $request)
    {

        //obtenemos todas las ciudades disponibles
        $ciudades = City::all(['id', 'name']);
        $asociation = AuthUser::where('role', 'asociacion')
            ->with('asociacion')
            ->find($request->id);
        return Inertia::render('asociation/show', [
            'asociation' => $asociation,
            'ciudades' => $ciudades,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //validar los datos 
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'number_phone' => 'required',
            'city' => 'required',
            'color' => 'required',
            'estado' => 'required',
            'direccion' => 'nullable|string',
            'descripcion' => 'nullable|string'
        ]);

        //actualizar usuario con los datos cal $request->id solo el campo email a AuthUser
        $usuario = AuthUser::findOrFail($request->id);
        $usuario->update([
            'email' => $request->email,
        ]);

        //actualizar asociaciones con los demas datos menos el email con el $request->id_asociacion
        $asociacion = Asociacion::findOrFail($request->id_asociacion);

        // Convertir el valor de estado a 1 o 0
        $estadoValue = ($request->estado == true) ? 1 : 0;

        $asociacion->update([
            'name' => $request->name,
            'number_phone' => $request->number_phone,
            'city' => $request->city,
            'color' => $request->color,
            'verified' => $estadoValue,
            'direccion' => $request->direccion,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->back()->with('success', 'Actualización completada con éxito');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function delete($dato)
    {
        $asociacion = Asociacion::findOrFail($dato); // Busca el registro o lanza un error 404
        $asociacion->delete(); // Elimina el registro
    }
}
