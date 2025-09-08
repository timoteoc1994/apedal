<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soporte extends Model
{
    protected $table = 'soportes';

    protected $fillable = [
        'user_id',
        'mensaje',
        'estado'
    ];

   public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }
}
