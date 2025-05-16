<?php

namespace App\Http\Controllers;

use App\Events\ActualizacionSolicitud;
use App\Events\SolicitudInmediata;
use App\Events\NuevasolicitudInmediata;
use App\Models\SolicitudRecoleccion;
use App\Events\EliminacionSolicitud;


use Illuminate\Http\Request;

class Prueba extends Controller
{
    public function index()
    {
        // Buscar la solicitud primero
        $solicitud = SolicitudRecoleccion::find(17);

        // Enviar el objeto completo, igual que con nuevas solicitudes
        //ActualizacionSolicitud::dispatch($solicitud, 3);
        //NuevaSolicitudInmediata::dispatch($solicitud, 2);
        EliminacionSolicitud::dispatch($solicitud, 4);
    }
}
