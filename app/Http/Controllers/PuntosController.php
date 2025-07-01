<?php

namespace App\Http\Controllers;

use App\Models\Puntos;
use Illuminate\Http\Request;
use Inertia\Inertia;


class PuntosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.



     * Display the specified resource.
     */
      // Crear el registro si no existe
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_hasta' => 'nullable|date',
            'puntos_registro_promocional' => 'required|integer',
            'puntos_reciclado_normal' => 'required|integer',
            'puntos_reciclado_referido' => 'required|integer',
        ]);
        $puntos = Puntos::create($data);
        return redirect()->route('puntos.edit')->with('success', 'Configuración creada correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Mostrar el formulario para crear o editar el único registro
    public function edit()
    {
        $puntos = Puntos::first();
        return Inertia::render('Puntos/Edit', [
            'puntos' => $puntos
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // Actualizar el registro existente
    public function update(Request $request)
    {
        $data = $request->validate([
            'fecha_hasta' => 'nullable|date',
            'puntos_registro_promocional' => 'required|integer',
            'puntos_reciclado_normal' => 'required|integer',
            'puntos_reciclado_referido' => 'required|integer',
        ]);
        $puntos = Puntos::first();
        if ($puntos) {
            $puntos->update($data);
        }
        return redirect()->route('puntos.edit')->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Puntos $puntos)
    {
        //
    }
}
