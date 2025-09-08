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
        'nickname',
        'telefono',
        'direccion',
        'ciudad',
        'logo_url',
        'referencias_ubicacion',
        'tipousuario',
        'genero',
        'fecha_nacimiento',
    ];



    /**
     * Obtener la cuenta de usuario asociada
     */
    public function authUser()
    {
        return $this->hasOne(AuthUser::class, 'profile_id')->where('role', 'ciudadano');
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
        $this->attributes['nickname'] = ucwords(strtolower($value));
    }
}
