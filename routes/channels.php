<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\AuthUser;

// Canal para el modelo AuthUser (no User)
Broadcast::channel('public-chat.{solicitudId}', function ($user, $solicitudId) {
    return [
        'id' => $user?->id ?? 'guest',
        'name' => $user?->name ?? 'Anónimo'
    ];
});

Broadcast::channel('public.chat.solicitud.{solicitudId}', function ($user, $solicitudId) {
    return true; // Acceso público a cualquier usuario
});

// Canal privado para recicladores - CORREGIDO
Broadcast::channel('reciclador.{id}', function ($user, $id) {
    // $user ya es una instancia de AuthUser automáticamente
    return (int) $user->id === (int) $id;
});

// Canal público para recicladores (alternativa)
Broadcast::channel('reciclador-{id}', function ($user, $id) {
    return true; // Permitir acceso a todos (canal público)
});
