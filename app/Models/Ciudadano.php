<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudadano extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'telefono',
        'direccion',
        'ciudad',
        'logo_url',
        'referencias_ubicacion',
    ];

    public function ciudad()
    {
        return $this->belongsTo(City::class, 'ciudad_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'ciudadano_id');
    }
    public function user()
    {
        return $this->hasOne(AuthUser::class, 'profile_id');  // Aquí se usa AuthUser en lugar de User
    }
}