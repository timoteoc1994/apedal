<?php

namespace App\Http\Controllers;

use App\Models\Asociacion;
use App\Models\AuthUser;
use App\Models\City;
use App\Models\FormulariMensual;
use App\Models\SolicitudRecoleccion;
use Illuminate\Http\Request;

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
                $queryInmediatas = SolicitudRecoleccion::where('asociacion_id', $authUserId)
                    ->where('es_inmediata', 1)->where('estado', 'completado');
                $queryAgendadas = SolicitudRecoleccion::where('asociacion_id', $authUserId)
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

                $datos[] = [
                    'asociacion' => $asoc->name,
                    'solicitudes_inmediatas' => $countInmediatas,
                    'solicitudes_agendadas' => $countAgendadas,
                    'suma_peso_kg' => $suma_peso_kg
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
}
