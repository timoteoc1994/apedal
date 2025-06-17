<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudadano extends Model
{
    use HasFactory;

    protected $table = 'ciudadanos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telefono',
        'direccion',
        'ciudad',
        'logo_url',
        'referencias_ubicacion',
    ];

    /**
     * Obtener las solicitudes de recolección de este ciudadano
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'ciudadano_id');
    }

    /**
     * Obtener la cuenta de usuario asociada
     */
    public function authUser()
    {
        return $this->hasOne(AuthUser::class, 'profile_id')->where('role', 'ciudadano');
    }
}
