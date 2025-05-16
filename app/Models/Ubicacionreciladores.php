<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacionreciladores extends Model
{
    use HasFactory;

    protected $fillable = [
        'auth_user_id',
        'latitude',
        'longitude',
        'timestamp',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'timestamp' => 'datetime',
    ];

    /**
     * Obtiene el usuario asociado a esta ubicación
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'auth_user_id');
    }
}
