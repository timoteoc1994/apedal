<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ejecutar el comando el último día de cada mes a las 23:59
Schedule::command('app:bonificacion')
    ->monthlyOn(date('t'), '23:59');