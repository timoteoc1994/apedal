<?php
// app/Models/Zona.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zona extends Model
{
    protected $fillable = [
        'nombre',
        'asociacion_id',
        'coordenadas',
    ];

    protected $casts = [
        'coordenadas' => 'array',
    ];

    public function asociacion(): BelongsTo
    {
        return $this->belongsTo(Asociacion::class);
    }

    // Método para obtener el color de la zona
    public function getColorAttribute()
    {
        return $this->asociacion ? $this->asociacion->color : '#3388ff';
    }
}
