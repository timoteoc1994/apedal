<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ActualizarPuntosCiudadano;

class TestActualizarPuntosCiudadano extends Command
{
    protected $signature = 'test:actualizar-puntos {user_id} {puntos}';
    protected $description = 'Probar el evento ActualizarPuntosCiudadano';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $puntos = $this->argument('puntos');

        $this->info("Ejecutando evento ActualizarPuntosCiudadano...");
        $this->info("User ID: {$userId}");
        $this->info("Puntos: {$puntos}");

        // Disparar el evento
        event(new ActualizarPuntosCiudadano($userId, $puntos));

        $this->info("Evento ejecutado correctamente!");
    }
}