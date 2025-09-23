<?php

namespace App\Http\Controllers;

use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\City;
use App\Models\FormulariMensual;
use App\Models\SolicitudRecoleccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EstadisticaControlador extends Controller
{
    function index(Request $request)
    {
       
        // Preparar asociaciones con su ciudad para filtrado en frontend
        $asociacion = Asociacion::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->name,
                'city' => $item->city,
            ];
        });

        $ciudades = City::all();
        $datos=[];
        // normalizar inputs a arrays
        $fasesAsociacion = $request->input('asociacion', []);
        if (!is_array($fasesAsociacion)) {
            $fasesAsociacion = [$fasesAsociacion];
        }
        $filtrosMes = $request->input('mes', []);
        if (!is_array($filtrosMes)) {
            $filtrosMes = [$filtrosMes];
        }
        $filtrosAnio = $request->input('anio', []);
        if (!is_array($filtrosAnio)) {
            $filtrosAnio = [$filtrosAnio];
        }

        if (!empty($fasesAsociacion)) {
            foreach ($fasesAsociacion as $asocId) {
                $asoc = Asociacion::with('authuser')->find($asocId);
                if (!$asoc || !$asoc->authuser) {
                    continue;
                }
                $authUserId = $asoc->authuser->id;

                // Base queries
                $queryInmediatas = SolicitudRecoleccion::with('materiales')->where('asociacion_id', $authUserId)
                    ->where('es_inmediata', 1)->where('estado', 'completado');
                $queryAgendadas = SolicitudRecoleccion::with('materiales')->where('asociacion_id', $authUserId)
                    ->where('es_inmediata', 0)->where('estado', 'completado');

                // Filtrar por meses (meses vienen como "02","03" etc.)
                if (count($filtrosMes)) {
                    $mesesNorm = array_map(function ($m) {
                        return intval(ltrim($m, '0'));
                    }, $filtrosMes);

                    $queryInmediatas->where(function ($q) use ($mesesNorm) {
                        foreach ($mesesNorm as $i => $mes) {
                            if ($i === 0) {
                                $q->whereMonth('fecha', $mes);
                            } else {
                                $q->orWhereMonth('fecha', $mes);
                            }
                        }
                    });

                    $queryAgendadas->where(function ($q) use ($mesesNorm) {
                        foreach ($mesesNorm as $i => $mes) {
                            if ($i === 0) {
                                $q->whereMonth('fecha', $mes);
                            } else {
                                $q->orWhereMonth('fecha', $mes);
                            }
                        }
                    });
                }

                // Filtrar por años
                if (count($filtrosAnio)) {
                    $aniosNorm = array_map('intval', $filtrosAnio);

                    $queryInmediatas->where(function ($q) use ($aniosNorm) {
                        foreach ($aniosNorm as $i => $anio) {
                            if ($i === 0) {
                                $q->whereYear('fecha', $anio);
                            } else {
                                $q->orWhereYear('fecha', $anio);
                            }
                        }
                    });

                    $queryAgendadas->where(function ($q) use ($aniosNorm) {
                        foreach ($aniosNorm as $i => $anio) {
                            if ($i === 0) {
                                $q->whereYear('fecha', $anio);
                            } else {
                                $q->orWhereYear('fecha', $anio);
                            }
                        }
                    });
                }

               


                // Contar resultados (más eficiente que get() + count())
                $countInmediatas = $queryInmediatas->count();
                $countAgendadas = $queryAgendadas->count();
                // Sumar peso_total_revisado de ambas consultas
                $sumInmediatas = (float) $queryInmediatas->sum('peso_total_revisado');
                $sumAgendadas  = (float) $queryAgendadas->sum('peso_total_revisado');
                $suma_peso_kg  = $sumInmediatas + $sumAgendadas;

                    // Tipos de materiales a consultar
                    $tiposMateriales = [
                        'eletronicos',
                        'electrodomesticos',
                        'metales',
                        'latas',
                        'pilas',
                        'vidrio',
                        'plasticosRigidos',
                        'plasticosSoplado',
                        'plasticosSuaves',
                        'botellasPET',
                        'tetrapak',
                        'papel',
                    ];

                    // Obtener IDs de solicitudes agendadas e inmediatas
                    $idsSolicitudes = $queryInmediatas->pluck('id')->merge($queryAgendadas->pluck('id'))->all();

                    // Inicializar array de sumas por tipo
                    $materialesSuma = array_fill_keys($tiposMateriales, 0);

                    if (count($idsSolicitudes)) {
                        $materiales = \App\Models\Material::whereIn('solicitud_id', $idsSolicitudes)
                            ->whereIn('tipo', $tiposMateriales)
                            ->select('tipo', \DB::raw('SUM(peso_revisado) as suma'))
                            ->groupBy('tipo')
                            ->get();

                        foreach ($materiales as $mat) {
                            $materialesSuma[$mat->tipo] = (float) $mat->suma;
                        }
                    }

                    $datos[] = [
                        'asociacion' => $asoc->name,
                        'solicitudes_inmediatas' => $countInmediatas,
                        'solicitudes_agendadas' => $countAgendadas,
                        'suma_peso_kg' => $suma_peso_kg,
                        'eletronicos' => $materialesSuma['eletronicos'] ?? 0,
                        'electrodomesticos' => $materialesSuma['electrodomesticos'] ?? 0,
                        'metales' => $materialesSuma['metales'] ?? 0,
                        'latas' => $materialesSuma['latas'] ?? 0,
                        'pilas' => $materialesSuma['pilas'] ?? 0,
                        'vidrio' => $materialesSuma['vidrio'] ?? 0,
                        'plasticosRigidos' => $materialesSuma['plasticosRigidos'] ?? 0,
                        'plasticosSoplado' => $materialesSuma['plasticosSoplado'] ?? 0,
                        'plasticosSuaves' => $materialesSuma['plasticosSuaves'] ?? 0,
                        'botellasPET' => $materialesSuma['botellasPET'] ?? 0,
                        'tetrapak' => $materialesSuma['tetrapak'] ?? 0,
                        'papel' => $materialesSuma['papel'] ?? 0,
                    ];

               
            }
            //dd($datos);
        }

        return inertia('EstadisticaSolicitudes/Index', [
            'asociacion' => $asociacion,
            'ciudades' => $ciudades,
            'datos' => $datos,
        ]);
    }


    /**
     * Devuelve asociaciones filtradas por ciudad(es).
     * Recibe query param: ciudades[]
     */
    public function asociacionesPorCiudad(Request $request)
    {
        $selected = $request->input('ciudades', []);
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        $query = Asociacion::query();
        if (count($selected)) {
            $query->whereIn('city', $selected);
        }

        $asociaciones = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->name,
                'city' => $item->city,
            ];
        });

        return response()->json($asociaciones);
    }
    public function descargar_pdf_estadisticas(Request $request)
    {
        // Esperamos recibir JSON con estructura: { filtros: {...}, fila: { asociacion, papel, tetrapak, ... } }
        $fila = $request->input('fila', null);

        // Si no viene fila, podemos usar un fallback (por ejemplo: datos desde request->all() o valores por defecto)
        if (!is_array($fila)) {
            $fila = [
                'asociacion' => $request->input('asociacion', 'Sin asociación'),
                'papel' => 0,
                'tetrapak' => 0,
                'botellasPET' => 0,
                'plasticosSuaves' => 0,
                'plasticosSoplado' => 0,
                'plasticosRigidos' => 0,
                'vidrio' => 0,
                'pilas' => 0,
                'latas' => 0,
                'metales' => 0,
                'electrodomesticos' => 0,
                'eletronicos' => 0,
                'suma_peso_kg' => 0,
                'solicitudes_inmediatas' => 0,
                'solicitudes_agendadas' => 0,
            ];
        }

        // Preparar datos que la vista Blade espera. Puedes añadir más claves según tu plantilla.
        $data = [
            'nombre' => $fila['asociacion'] ?? 'Sin asociación',
            'papel' => $fila['papel'] ?? 0,
            'tetrapak' => $fila['tetrapak'] ?? 0,
            'botellasPET' => $fila['botellasPET'] ?? 0,
            'plasticosSuaves' => $fila['plasticosSuaves'] ?? 0,
            'plasticosSoplado' => $fila['plasticosSoplado'] ?? 0,
            'plasticosRigidos' => $fila['plasticosRigidos'] ?? 0,
            'vidrio' => $fila['vidrio'] ?? 0,
            'pilas' => $fila['pilas'] ?? 0,
            'latas' => $fila['latas'] ?? 0,
            'metales' => $fila['metales'] ?? 0,
            'electrodomesticos' => $fila['electrodomesticos'] ?? 0,
            'eletronicos' => $fila['eletronicos'] ?? 0,
            'suma_peso_kg' => $fila['suma_peso_kg'] ?? 0,
            'solicitudes_inmediatas' => $fila['solicitudes_inmediatas'] ?? 0,
            'solicitudes_agendadas' => $fila['solicitudes_agendadas'] ?? 0,
            // Pasar filtros completos por si la vista los necesita
            'filtros' => $request->input('filtros', []),
        ];

        // Generar PDF y habilitar carga remota si es necesario
        $pdf = Pdf::loadView('pdf.documentomarketing', $data);
        try {
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        } catch (\Throwable $e) {
            // Si la función no está disponible, continuar sin bloquear la generación
        }

        // Crear nombre del archivo con fecha y nombre de asociación
        $nombreAsociacion = $data['nombre'] ?? 'reporte';
        $nombreAsociacion = preg_replace('/[^a-zA-Z0-9]/', '_', $nombreAsociacion);
        $fecha = date('Y-m-d');
        $nombreArchivo = "Reporte_{$nombreAsociacion}_{$fecha}.pdf";

        // Devolver el PDF con headers de descarga más específicos
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
