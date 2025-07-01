<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asociacion extends Model
{
    use HasFactory;

    protected $table = 'asociaciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'number_phone',
        'direccion',
        'city',
        'descripcion',
        'logo_url',
        'verified',
        'color',
        'imagen_referencial',
        'dias_atencion',
        'hora_apertura',
        'hora_cierre',
        'materiales_aceptados',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
    ];

    /**
     * Obtener los recicladores de esta asociación
     */
    public function recicladores()
    {
        return $this->hasMany(Reciclador::class, 'asociacion_id');
    }

    /**
     * Obtener las solicitudes asignadas a esta asociación
     */

    /**
     * Obtener la cuenta de usuario asociada
     */
    public function authUser()
    {
        return $this->hasOne(AuthUser::class, 'profile_id')->where('role', 'asociacion');
    }
    public function zonas(): HasMany
    {
        return $this->hasMany(Zona::class);
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }
}
