<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'es_inmediata',
        'calificacion_reciclador',
        'calificacion_ciudadano',
        'peso_total_revisado',
    ];

    protected $casts = [
        'fecha'                => 'date',
        'fecha_completado'     => 'datetime',
        'latitud'              => 'float',
        'longitud'             => 'float',
        'peso_total'           => 'float',
        'es_inmediata'         => 'boolean',
        'peso_total_revisado'  => 'float',
        'imagen' => 'array', 
    ];

    /**
     * Relación al solicitante en auth_users
     */
    public function usuarioAuth(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AuthUser::class, 'user_id');
    }

    /**
     * Relación a la asociación (auth_users con role = 'asociacion')
     */
    public function asociacionAuth(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AuthUser::class, 'asociacion_id');
    }

    /**
     * Relación al reciclador (auth_users con role = 'reciclador')
     */
    public function recicladorAuth(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AuthUser::class, 'reciclador_id');
    }

    /**
     * Relación con Zona
     */
    public function zona(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Zona::class, 'zona_id');
    }

    /**
     * Relación con materiales (si la necesitas)
     */
    public function materiales()
    {
        return $this->hasMany(\App\Models\Material::class, 'solicitud_id');
    }
}
