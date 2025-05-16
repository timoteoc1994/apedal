<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;
    protected $table = 'materiales';
    protected $fillable = [
        'solicitud_id',
        'tipo',
        'peso',
    ];

    protected $casts = [
        'peso' => 'float',
    ];

    // Relación con solicitud
    public function solicitud()
    {
        return $this->belongsTo(SolicitudRecoleccion::class, 'solicitud_id');
    }
}
