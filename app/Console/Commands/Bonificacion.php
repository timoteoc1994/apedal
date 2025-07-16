<?php

namespace App\Console\Commands;

use App\Events\ActualizarPuntosCiudadano;
use App\Models\AuthUser;
use App\Models\Ciudadano;
use App\Models\SolicitudRecoleccion;
use App\Services\FirebaseService;
use Illuminate\Console\Command;

class Bonificacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bonificacion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        //obtener todos los perfiles de ciudadanos
        $ciudadanos=AuthUser::where('role', 'ciudadano')
            ->where('fcm_token', '!=', null)
            ->with('ciudadano')
            ->get();
        // Iterar sobre cada ciudadano
        foreach ($ciudadanos as $ciudadano) {
             // Sumar solicitudes de hoy (ignorando la hora)
        $cantidad = SolicitudRecoleccion::whereMonth('created_at', now()->month)
            ->where('estado', 'completado')
            ->where('user_id', $ciudadano->id)
            ->sum('peso_total_revisado');
            //if si cantidad esta entre 50 a 150
            if ($cantidad >= 50 && $cantidad <= 150) {

                //sumar los puntos que hiso en este mes
                $puntosMes = SolicitudRecoleccion::whereMonth('created_at', now()->month)
                    ->where('estado', 'completado')
                    ->where('user_id', $ciudadano->id)
                    ->sum('calificacion_reciclador');
                //multiplcamos por 1.5 el total de puntosmes y restamos el puntosmes hacemos que sea un entero y ese valor anadir al ciudad
                $bonificacion = (int) ($puntosMes * 1.5) - $puntosMes;
                $ciudadano->puntos += $bonificacion; 
                $ciudadano->save(); // Guardar cambios
                //ejecutar evento
                //evento para actualizar los puntos del ciudadano actual
                event(new ActualizarPuntosCiudadano($ciudadano->id, $ciudadano->puntos));
                //enviamos notificacion al ciudadano
                FirebaseService::sendNotification($ciudadano->id, [
                    'title' => 'Bonificación aplicada',
                    'body' => "Se ha aplicado una bonificación de $bonificacion puntos.",
                    'data' => [
                    ]
                ]);

            } elseif ($cantidad >= 151 && $cantidad <= 300) {
                 //sumar los puntos que hiso en este mes
                $puntosMes = SolicitudRecoleccion::whereMonth('created_at', now()->month)
                    ->where('estado', 'completado')
                    ->where('user_id', $ciudadano->id)
                    ->sum('calificacion_reciclador');
                //multiplcamos por 1.5 el total de puntosmes y restamos el puntosmes hacemos que sea un entero y ese valor anadir al ciudad
                $bonificacion = (int) ($puntosMes * 2) - $puntosMes;
                $ciudadano->puntos += $bonificacion; 
                $ciudadano->save(); // Guardar cambios
                //evento para actualizar los puntos del ciudadano actual
                event(new ActualizarPuntosCiudadano($ciudadano->id, $ciudadano->puntos));
                //enviamos notificacion al ciudadano
                FirebaseService::sendNotification($ciudadano->id, [
                    'title' => 'Bonificación aplicada',
                    'body' => "Se ha aplicado una bonificación de $bonificacion puntos.",
                    'data' => [
                    ]
                ]);
            }elseif ($cantidad > 300) {
                 //sumar los puntos que hiso en este mes
                $puntosMes = SolicitudRecoleccion::whereMonth('created_at', now()->month)
                    ->where('estado', 'completado')
                    ->where('user_id', $ciudadano->id)
                    ->sum('calificacion_reciclador');
                //multiplcamos por 1.5 el total de puntosmes y restamos el puntosmes hacemos que sea un entero y ese valor anadir al ciudad
                $bonificacion = (int) ($puntosMes * 3) - $puntosMes;
                $ciudadano->puntos += $bonificacion; 
                $ciudadano->save(); // Guardar cambios
                //evento para actualizar los puntos del ciudadano actual
                event(new ActualizarPuntosCiudadano($ciudadano->id, $ciudadano->puntos));
                //enviamos notificacion al ciudadano

            FirebaseService::sendNotification($ciudadano->id, [
                'title' => 'Bonificación aplicada del mes ',
                'body' => "Se ha aplicado una bonificación de $bonificacion puntos.",
                'data' => [
                ]
            ]);
            } 

        }
        $this->info('Bonificaciones aplicadas correctamente a los ciudadanos.');
       
    }
}
