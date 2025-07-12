<?php

namespace App\Http\Controllers;

use App\Models\AuthUser;
use App\Models\SolicitudRecoleccion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    function index()
    {
        // Estadísticas por día de los últimos 8 días
        $solicitudesPorDia = SolicitudRecoleccion::selectRaw('
                DATE(created_at) as fecha,
                estado,
                COUNT(*) as count
            ')
            ->where('created_at', '>=', now()->subDays(8))
            ->groupByRaw('DATE(created_at), estado')
            ->orderByRaw('DATE(created_at) asc')
            ->get();

        // Formatear datos para el gráfico
        $datosGrafico = [];
        $fechas = [];
        $estados = ['completado', 'cancelado', 'buscando_reciclador', 'pendiente'];

        // Generar las últimas 8 fechas
        for ($i = 9; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $fechas[] = now()->subDays($i)->format('d/m');

            foreach ($estados as $estado) {
                $count = SolicitudRecoleccion::whereDate('created_at', $fecha)
                    ->where('estado', $estado)
                    ->count();

                $datosGrafico[$estado][] = $count;
            }
        }

        //obtener cuantos recicladores, asociaciones y ciudadanos hay
        $recicladoresCount = AuthUser::where('role', 'reciclador')->count();
        $asociacionesCount = AuthUser::where('role', 'asociacion')->count();
        $ciudadanosCount = AuthUser::where('role', 'ciudadano')->count();

        return Inertia::render('Dashboard', [
            'solicitudes' => [
                'fechas' => $fechas,
                'datos' => $datosGrafico,
                'resumen' => $solicitudesPorDia->groupBy('estado')->map->sum('count')
            ],
            'recicladoresCount' => $recicladoresCount,
            'asociacionesCount' => $asociacionesCount,
            'ciudadanosCount' => $ciudadanosCount
        ]);
    }
}

