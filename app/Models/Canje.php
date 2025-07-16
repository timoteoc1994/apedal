<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Canje extends Model
{
    use HasFactory;

    protected $table = 'canje';

    protected $fillable = [
        'auth_user_id',
        'user_id',
        'producto_id',
        'puntos_canjeados',
        'codigo',
        'estado',
        'fecha_canjeado'
    ];

    public function authuser(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'auth_user_id'); 
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
