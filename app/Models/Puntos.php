<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puntos extends Model
{
    protected $fillable = [
        'fecha_hasta',
        'puntos_registro_promocional',
        'puntos_reciclado_normal',
        'puntos_reciclado_referido',
    ];
}
