<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'auth_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'profile_id',
        'fcm_token',
        'email_verification_code',
        'puntos', // Añadir puntos al modelo
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Obtener el perfil del usuario según su rol
     */
    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class, 'profile_id');
    }

    /**
     * Relación con el modelo Reciclador
     */
    public function reciclador(): BelongsTo
    {
        return $this->belongsTo(Reciclador::class, 'profile_id');
    }

    /**
     * Relación con el modelo Asociación
     */
    public function asociacion(): BelongsTo
    {
        return $this->belongsTo(Asociacion::class, 'profile_id');
    }

    public function profile()
    {
        switch ($this->role) {
            case 'ciudadano':
                return $this->belongsTo(Ciudadano::class, 'profile_id');
            case 'reciclador':
                return $this->belongsTo(Reciclador::class, 'profile_id');
            case 'asociacion':
                return $this->belongsTo(Asociacion::class, 'profile_id');
            default:
                return null;
        }
    }

    /**
     * Mutador para guardar el email siempre en minúsculas
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

}
