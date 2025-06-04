<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    use HasFactory;
    protected $table = 'mensajes';

    protected $fillable = ['mensaje', 'user_id', 'solicitud_id'];

    public function user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
    public function solicitud()
    {
        return $this->belongsTo(SolicitudRecoleccion::class, 'solicitud_id');
    }
}
