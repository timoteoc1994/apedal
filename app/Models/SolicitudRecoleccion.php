<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AuthUser;
use App\Models\Zona;
use App\Models\Material;

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
        'estado',
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
        'fecha' => 'date',
        'fecha_completado' => 'datetime',
        'latitud' => 'float',
        'longitud' => 'float',
        'peso_total' => 'float',
        'es_inmediata' => 'boolean',
        'peso_total_revisado' => 'float',
    ];

    // Relación con materiales
    public function materiales()
    {
        return $this->hasMany(Material::class, 'solicitud_id');
    }

    // ✅ Relación con el usuario (ciudadano que creó la solicitud)
    public function usuarioAuth()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    // ✅ Relación con la asociación
    public function asociacionAuth()
    {
        return $this->belongsTo(AuthUser::class, 'asociacion_id');
    }

    // ✅ Relación con la zona
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    // ✅ Relación con el reciclador
    public function recicladorAuth()
    {
        return $this->belongsTo(AuthUser::class, 'reciclador_id');
    }

    // ❌ Esta relación es redundante con recicladorAuth (puedes eliminarla si no se usa)
    public function recicladorAsignado()
    {
        return $this->belongsTo(AuthUser::class, 'reciclador_id')
            ->where('role', 'reciclador')
            ->with('reciclador:id,name,telefono,logo_url');
    }

    // ✅ Accesor para las imágenes (urls públicas)
    public function getImagenesUrlsAttribute()
    {
        $imagenes = is_array($this->imagen)
            ? $this->imagen
            : json_decode($this->imagen, true);

        if (!is_array($imagenes)) return [];

        return array_map(function ($img) {
            return asset('storage/' . $img);
        }, $imagenes);
    }
}
