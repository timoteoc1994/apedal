<?php
// app/Http/Controllers/ZonaController.php
namespace App\Http\Controllers;

use App\Models\Zona;
use App\Models\Asociacion;
use App\Models\City;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ZonaController extends Controller
{
    //index para mostrar por ciudades cada zona
    public function index()
    {
        $ciudades = City::all();  
        return Inertia::render('Zonas/vista', [
            'ciudades' => $ciudades
        ]);
    }
    // Mostrar todas las zonas en el mapa
       public function mapa($ciudad)
    {
        // Obtener zonas que pertenecen a asociaciones de la ciudad específica
        $zonas = Zona::with(['asociacion' => function($query) use ($ciudad) {
            $query->where('city', $ciudad);
        }])
        ->whereHas('asociacion', function($query) use ($ciudad) {
            $query->where('city', $ciudad);
        })
        ->get();

        // Obtener solo las asociaciones de la ciudad específica
        $asociaciones = Asociacion::where('city', $ciudad)->get();

        return Inertia::render('Zonas/Mapa', [
            'zonas' => $zonas,
            'asociaciones' => $asociaciones,
            'ciudad' => $ciudad
        ]);
    }

    // Obtener una zona específica
    public function obtener($id)
    {
        $zona = Zona::with('asociacion')->findOrFail($id);

        return response()->json($zona);
    }

    // Guardar una nueva zona
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'asociacion_id' => 'required|exists:asociaciones,id',
            'coordenadas' => 'required|array',
        ]);

        Zona::create($validated);

        //regresar para atras
        return back()->with('success', 'Zona creada exitosamente.');
    }

    // Actualizar una zona existente
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'asociacion_id' => 'required|exists:asociaciones,id',
            'coordenadas' => 'required|array',
        ]);

        $zona = Zona::findOrFail($id);
        $zona->update($validated);

        //regresar para atras
        return back()->with('success', 'Zona actualizada exitosamente.');

    }

    // Eliminar una zona
    public function destroy($id)
    {
        $zona = Zona::findOrFail($id);
        $zona->delete();

        //regresar para atras
        return back()->with('success', 'Zona eliminada exitosamente.');
    }

    // Obtener todas las zonas (para API)
    public function obtenerZonas()
    {
        $zonas = Zona::with('asociacion')->get()->map(function ($zona) {
            return [
                'id' => $zona->id,
                'nombre' => $zona->nombre,
                'asociacion' => $zona->asociacion->name,
                'color' => $zona->asociacion->color,
                'coordenadas' => $zona->coordenadas
            ];
        });

        return response()->json($zonas);
    }
}
