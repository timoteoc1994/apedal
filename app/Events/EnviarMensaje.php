<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mensaje;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Log;

class EnviarMensaje implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Mensaje $mensaje, AuthUser $user)
    {

        $this->mensaje = $mensaje;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {

        return [
            new Channel('chat-solicitud-' . $this->mensaje->solicitud_id),
        ];
    }


    /**
     * ✅ CORREGIDO: Estructura de datos para el WebSocket
     */
    public function broadcastWith(): array
    {
        return [
            'mensaje' => [
                'id' => $this->mensaje->id,
                'texto' => $this->mensaje->mensaje, // Cambiado a 'texto' para coincidir con la API
                'created_at' => $this->mensaje->created_at->toDateTimeString(),
                'user_id' => $this->mensaje->user_id
            ],
            'usuario' => $this->getUserProfileName($this->user) // Cambiado a 'usuario' para coincidir
        ];
    }

    /**
     * Obtener el nombre del perfil del usuario
     */
    private function getUserProfileName(AuthUser $user): string
    {
        try {
            switch ($user->role) {
                case 'ciudadano':
                    $ciudadano = $user->ciudadano;
                    return $ciudadano ? $ciudadano->name : 'Ciudadano';

                case 'reciclador':
                    $reciclador = $user->reciclador;
                    return $reciclador ? $reciclador->name : 'Reciclador';

                case 'asociacion':
                    $asociacion = $user->asociacion;
                    return $asociacion ? $asociacion->name : 'Asociación';

                default:
                    return 'Usuario ' . ucfirst($user->role);
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo nombre de perfil en evento: ' . $e->getMessage());
            return 'Usuario';
        }
    }
}
