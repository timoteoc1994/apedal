<?php

namespace App\Http\Controllers;

use App\Models\FormulariMensual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FormulariMensualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
{
    $user = Auth::user();
    $perPage = $request->input('per_page', 8);

    $formularios = FormulariMensual::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

    // Transformar solo los campos necesarios
    $formularios->getCollection()->transform(function ($item) {
        return [
            'id' => $item->id,
            'mes' => $item->mes,
            'anio' => $item->anio,
            'lugar' => $item->lugar,
            'total_kilos' => $item->total_kilos,
            'total_monto' => $item->total_monto,
            'created_at' => $item->created_at,
            // Agrega aquí cualquier otro campo que sí quieras mostrar
        ];
    });

    return response()->json($formularios);
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
    Log::info('Los datos que vienen del formulario son: ', $request->all());

    try {
        Log::info('FormulariMensualController@store');
        $validated = $request->validate([
            'mes' => 'required|string',
            'anio' => 'required|string',
            'lugar' => 'required|string',
            'num_recicladores' => 'required|integer',
            'total_kilos' => 'required|numeric',
            'total_monto' => 'required|numeric',
            // Materiales (kilos y precios)
            'carton_kilos' => 'nullable|numeric',
            'carton_precio' => 'nullable|numeric',
            'duplex_cubeta_kilos' => 'nullable|numeric',
            'duplex_cubeta_precio' => 'nullable|numeric',
            'papel_comercio_kilos' => 'nullable|numeric',
            'papel_comercio_precio' => 'nullable|numeric',
            'papel_bond_kilos' => 'nullable|numeric',
            'papel_bond_precio' => 'nullable|numeric',
            'papel_mixto_kilos' => 'nullable|numeric',
            'papel_mixto_precio' => 'nullable|numeric',
            'papel_multicolor_kilos' => 'nullable|numeric',
            'papel_multicolor_precio' => 'nullable|numeric',
            'tetrapak_kilos' => 'nullable|numeric',
            'tetrapak_precio' => 'nullable|numeric',
            'plastico_soplado_kilos' => 'nullable|numeric',
            'plastico_soplado_precio' => 'nullable|numeric',
            'plastico_duro_kilos' => 'nullable|numeric',
            'plastico_duro_precio' => 'nullable|numeric',
            'plastico_fino_kilos' => 'nullable|numeric',
            'plastico_fino_precio' => 'nullable|numeric',
            'pet_kilos' => 'nullable|numeric',
            'pet_precio' => 'nullable|numeric',
            'vidrio_kilos' => 'nullable|numeric',
            'vidrio_precio' => 'nullable|numeric',
            'chatarra_kilos' => 'nullable|numeric',
            'chatarra_precio' => 'nullable|numeric',
            'bronce_kilos' => 'nullable|numeric',
            'bronce_precio' => 'nullable|numeric',
            'cobre_kilos' => 'nullable|numeric',
            'cobre_precio' => 'nullable|numeric',
            'aluminio_kilos' => 'nullable|numeric',
            'aluminio_precio' => 'nullable|numeric',
            'pvc_kilos' => 'nullable|numeric',
            'pvc_precio' => 'nullable|numeric',
            'baterias_kilos' => 'nullable|numeric',
            'baterias_precio' => 'nullable|numeric',
            'lona_kilos' => 'nullable|numeric',
            'lona_precio' => 'nullable|numeric',
            'caucho_kilos' => 'nullable|numeric',
            'caucho_precio' => 'nullable|numeric',
            'fomix_kilos' => 'nullable|numeric',
            'fomix_precio' => 'nullable|numeric',
            'polipropileno_kilos' => 'nullable|numeric',
            'polipropileno_precio' => 'nullable|numeric',
        ]);
        Log::info('paso el validador');
        $user = Auth::user();
        $validated['user_id'] = $user->id;
        $validated['asociacion_id'] = $user->profile_id;

        // Manejo de archivos adjuntos
        $nombresArchivos = [];
        if ($request->hasFile('archivos_adjuntos')) {
            foreach ($request->file('archivos_adjuntos') as $index => $archivo) {
                if ($archivo) {
                    $nombreUnico = time() . '_' . $index . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
                    // Guarda en storage/app/public/FormularioAdjuntos
                    $ruta = $archivo->storeAs('FormularioAdjuntos', $nombreUnico, 'public');
                    $nombresArchivos[] = $ruta; // Guarda la ruta relativa
                }
            }
        }
        $validated['archivos_adjuntos'] = json_encode($nombresArchivos);

        $formulario = FormulariMensual::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Formulario mensual guardado correctamente',
            'data' => $formulario
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error en FormulariMensualController@store: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error al guardar el formulario mensual.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $formulario = FormulariMensual::findOrFail($id);
    return response()->json([
        'success' => true,
        'data' => $formulario
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormulariMensual $formulariMensual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, $id)
{
    $formulario = FormulariMensual::findOrFail($id);
    Log::info('Los datos que vienen del formulario son: ', $request->except(['archivos_adjuntos']));
    Log::info('Archivos a eliminar:', $request->input('archivos_a_eliminar', []));
    Log::info('Archivos subidos:', $request->file('archivos_adjuntos', []));

    $validated = $request->validate([
        'mes' => 'sometimes|string',
        'anio' => 'sometimes|string',
        'lugar' => 'sometimes|string',
        'num_recicladores' => 'sometimes|integer',
        'total_kilos' => 'sometimes|numeric',
        'total_monto' => 'sometimes|numeric',
        'carton_kilos' => 'sometimes|nullable|numeric',
        'carton_precio' => 'sometimes|nullable|numeric',
        'duplex_cubeta_kilos' => 'sometimes|nullable|numeric',
        'duplex_cubeta_precio' => 'sometimes|nullable|numeric',
        'papel_comercio_kilos' => 'sometimes|nullable|numeric',
        'papel_comercio_precio' => 'sometimes|nullable|numeric',
        'papel_bond_kilos' => 'sometimes|nullable|numeric',
        'papel_bond_precio' => 'sometimes|nullable|numeric',
        'papel_mixto_kilos' => 'sometimes|nullable|numeric',
        'papel_mixto_precio' => 'sometimes|nullable|numeric',
        'papel_multicolor_kilos' => 'sometimes|nullable|numeric',
        'papel_multicolor_precio' => 'sometimes|nullable|numeric',
        'tetrapak_kilos' => 'sometimes|nullable|numeric',
        'tetrapak_precio' => 'sometimes|nullable|numeric',
        'plastico_soplado_kilos' => 'sometimes|nullable|numeric',
        'plastico_soplado_precio' => 'sometimes|nullable|numeric',
        'plastico_duro_kilos' => 'sometimes|nullable|numeric',
        'plastico_duro_precio' => 'sometimes|nullable|numeric',
        'plastico_fino_kilos' => 'sometimes|nullable|numeric',
        'plastico_fino_precio' => 'sometimes|nullable|numeric',
        'pet_kilos' => 'sometimes|nullable|numeric',
        'pet_precio' => 'sometimes|nullable|numeric',
        'vidrio_kilos' => 'sometimes|nullable|numeric',
        'vidrio_precio' => 'sometimes|nullable|numeric',
        'chatarra_kilos' => 'sometimes|nullable|numeric',
        'chatarra_precio' => 'sometimes|nullable|numeric',
        'bronce_kilos' => 'sometimes|nullable|numeric',
        'bronce_precio' => 'sometimes|nullable|numeric',
        'cobre_kilos' => 'sometimes|nullable|numeric',
        'cobre_precio' => 'sometimes|nullable|numeric',
        'aluminio_kilos' => 'sometimes|nullable|numeric',
        'aluminio_precio' => 'sometimes|nullable|numeric',
        'pvc_kilos' => 'sometimes|nullable|numeric',
        'pvc_precio' => 'sometimes|nullable|numeric',
        'baterias_kilos' => 'sometimes|nullable|numeric',
        'baterias_precio' => 'sometimes|nullable|numeric',
        'lona_kilos' => 'sometimes|nullable|numeric',
        'lona_precio' => 'sometimes|nullable|numeric',
        'caucho_kilos' => 'sometimes|nullable|numeric',
        'caucho_precio' => 'sometimes|nullable|numeric',
        'fomix_kilos' => 'sometimes|nullable|numeric',
        'fomix_precio' => 'sometimes|nullable|numeric',
        'polipropileno_kilos' => 'sometimes|nullable|numeric',
        'polipropileno_precio' => 'sometimes|nullable|numeric',
    ]);

      // 1. Obtener los archivos actuales
    $archivosActuales = json_decode($formulario->archivos_adjuntos ?? '[]', true);

    // 2. Eliminar archivos seleccionados
    $archivosAEliminar = $request->input('archivos_a_eliminar', []);
    foreach ($archivosAEliminar as $archivo) {
        $rutaCompleta = public_path('storage/' . $archivo);
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
        // Eliminar del array de archivos actuales
        $archivosActuales = array_filter($archivosActuales, function ($item) use ($archivo) {
            return $item !== $archivo;
        });
    }

    // 3. Agregar archivos nuevos
    $nuevosArchivos = [];
    if ($request->hasFile('archivos_adjuntos')) {
        foreach ($request->file('archivos_adjuntos') as $index => $archivo) {
            if ($archivo) {
                $nombreUnico = time() . '_' . $index . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
                $ruta = $archivo->storeAs('FormularioAdjuntos', $nombreUnico, 'public');
                $nuevosArchivos[] = $ruta;
            }
        }
    }

    // 4. Fusionar archivos restantes + nuevos
    $archivosFinales = array_values(array_merge($archivosActuales, $nuevosArchivos));
    $validated['archivos_adjuntos'] = json_encode($archivosFinales);

    // 5. Actualizar el formulario
    $formulario->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Formulario actualizado correctamente',
        'data' => $formulario
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $formulario = FormulariMensual::findOrFail($id);
    $formulario->delete();

    return response()->json([
        'success' => true,
        'message' => 'Formulario eliminado correctamente'
    ]);
}
}
