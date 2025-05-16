<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudRecoleccion extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_recoleccion';

    protected $fillable = [
        'user_id',
        'asociacion_id',
        'zona_id',
        'reciclador_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'direccion',
        'referencia',
        'latitud',
        'longitud',
        'peso_total',
        'imagen',
        'estado', // pendiente, asignado, en_camino, completado, cancelado
        'fecha_completado',
        'comentarios',
        'ciudad',
        'ids_disponibles',
        'es_inmediata'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_completado' => 'datetime',
        'latitud' => 'float',
        'longitud' => 'float',
        'peso_total' => 'float',
        'es_inmediata' => 'boolean',
    ];

    // Relación con materiales
    public function materiales()
    {
        return $this->hasMany(Material::class, 'solicitud_id');
    }

    // Relación con el usuario (ciudadano)
    public function authUser()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    // Relación con el reciclador
    public function reciclador()
    {
        return $this->belongsTo(Reciclador::class, 'reciclador_id');
    }
}
