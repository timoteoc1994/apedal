<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reciclador extends Model
{
    use HasFactory;

    protected $table = 'recicladores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telefono',
        'ciudad',
        'asociacion_id',
        'status',
        'estado',
        'logo_url',
        'is_new',
        'genero',
        'fecha_nacimiento',
    ];

    /**
     * Obtener la asociación a la que pertenece este reciclador
     */
    public function asociacion()
    {
        return $this->belongsTo(Asociacion::class, 'asociacion_id');
    }


    /**
     * Obtener la cuenta de usuario asociada
     */
    public function authUser()
    {
        return $this->hasOne(AuthUser::class, 'profile_id')->where('role', 'reciclador');
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }
}
