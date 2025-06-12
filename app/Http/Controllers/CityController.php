<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->search;

        $cities = city::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            })
            ->paginate(10);



        return Inertia::render('city/index', [
            'cities' => $cities,
            'filters' => $request->only(['search'])
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //obtener la id del usuario autenticado
        $user = auth()->user()->id;
        //validar datos
        $datos = $request->validate([
            'name' => 'required|string|unique:cities,name|max:255',
        ]);
        $datos['user_id'] = $user;
        City::create($datos);
        return redirect()->back()->with('success', 'Ciudad creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(City $city)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($dato)
    {
        $asociacion = city::findOrFail($dato); // Busca el registro o lanza un error 404
        $asociacion->delete(); // Elimina el registro
    }

    public function getCities()
    {
        // Obtén todas las ciudades solo con ID y name
        $cities = City::all(['id', 'name']);
        return response()->json($cities);
    }
}
