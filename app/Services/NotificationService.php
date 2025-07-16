<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\AuthUser;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Enviar notificación a un usuario específico por ID
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $user = AuthUser::find($userId);

        if (!$user || !$user->fcm_token) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado o sin token FCM',
            ];
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data);
    }

    /**
     * Enviar notificación a un token específico
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $response = $this->messaging->send($message);

            return [
                'success' => true,
                'message' => 'Notificación enviada correctamente',
                'response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al enviar notificación: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar notificación a todos los usuarios con un rol específico
     */
    public function sendToRole($role, $title, $body, $data = [])
    {
        $users = AuthUser::where('role', $role)
            ->whereNotNull('fcm_token')
            ->get();

        $results = [];

        foreach ($users as $user) {
            $results[] = $this->sendToToken($user->fcm_token, $title, $body, $data);
        }

        return [
            'success' => true,
            'total' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Enviar notificación a múltiples tokens
     */
    public function sendToMultipleTokens($tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'No se proporcionaron tokens',
            ];
        }

        $results = [];

        foreach ($tokens as $token) {
            $results[] = $this->sendToToken($token, $title, $body, $data);
        }

        return [
            'success' => true,
            'total' => count($results),
            'results' => $results,
        ];
    }
}
