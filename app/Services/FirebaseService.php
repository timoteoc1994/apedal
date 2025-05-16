<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\AuthUser;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

class FirebaseService
{
    /**
     * Obtiene la instancia de Firebase Messaging
     *
     * @return \Kreait\Firebase\Messaging
     */
    private static function getMessaging()
    {
        try {
            // Ruta al archivo de credenciales
            $credentialsPath = storage_path('app/appedal-ffe02-firebase-adminsdk-fbsvc-98fe6577e7.json');

            // Crear instancia de Firebase
            $factory = (new Factory)->withServiceAccount($credentialsPath);

            // Crear y retornar instancia de Messaging
            return $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Error al inicializar Firebase Messaging: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Envía una notificación push a un usuario específico
     *
     * @param int $userId ID del usuario en la tabla auth_users
     * @param array $notification Array con title, body y data
     * @return bool Éxito de la operación
     */
    public static function sendNotification($userId, array $notification)
    {
        try {
            // Obtener el token FCM del usuario
            $user = AuthUser::find($userId);

            if (!$user || empty($user->fcm_token)) {
                Log::warning('No se pudo enviar notificación: Token FCM no disponible', [
                    'user_id' => $userId
                ]);
                return false;
            }

            // Crear instancia de Messaging
            $messaging = self::getMessaging();

            // Crear notificación
            $fcmNotification = Notification::create(
                $notification['title'] ?? 'Notificación',
                $notification['body'] ?? ''
            );

            // Crear mensaje con notificación y datos
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification($fcmNotification)
                ->withData($notification['data'] ?? []);

            // Enviar mensaje
            $result = $messaging->send($message);

            Log::info('Notificación enviada correctamente', [
                'user_id' => $userId,
                'message_id' => $result
            ]);

            return true;
        } catch (MessagingException $e) {
            Log::error('Error al enviar notificación a Firebase: ' . $e->getMessage(), [
                'user_id' => $userId,
                'error_code' => $e->getCode(),
                'error_details' => $e->getMessage()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción al enviar notificación: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Envía una notificación a múltiples usuarios (por ejemplo, recicladores cercanos)
     *
     * @param array $userIds Array de IDs de usuarios
     * @param array $notification Array con title, body y data
     * @return array Array con resultados de cada envío
     */
    public static function sendMultipleNotifications(array $userIds, array $notification)
    {
        $results = [];

        foreach ($userIds as $userId) {
            $results[$userId] = self::sendNotification($userId, $notification);
        }

        return $results;
    }

    /**
     * Envía una notificación por tópico (por ejemplo, todos los recicladores de una ciudad)
     *
     * @param string $topic Tópico (por ejemplo: 'recicladores_quito')
     * @param array $notification Array con title, body y data
     * @return bool Éxito de la operación
     */
    public static function sendTopicNotification($topic, array $notification)
    {
        try {
            // Crear instancia de Messaging
            $messaging = self::getMessaging();

            // Crear notificación
            $fcmNotification = Notification::create(
                $notification['title'] ?? 'Notificación',
                $notification['body'] ?? ''
            );

            // Crear mensaje con notificación y datos
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($fcmNotification)
                ->withData($notification['data'] ?? []);

            // Enviar mensaje
            $result = $messaging->send($message);

            Log::info('Notificación a tópico enviada correctamente', [
                'topic' => $topic,
                'message_id' => $result
            ]);

            return true;
        } catch (MessagingException $e) {
            Log::error('Error al enviar notificación a tópico: ' . $e->getMessage(), [
                'topic' => $topic,
                'error_code' => $e->getCode(),
                'error_details' => $e->getMessage()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción al enviar notificación a tópico: ' . $e->getMessage(), [
                'exception' => $e,
                'topic' => $topic
            ]);
            return false;
        }
    }

    /**
     * Suscribe un token FCM a un tópico
     *
     * @param string $token Token FCM del dispositivo
     * @param string $topic Nombre del tópico
     * @return bool Éxito de la operación
     */
    public static function subscribeToTopic($token, $topic)
    {
        try {
            // Crear instancia de Messaging
            $messaging = self::getMessaging();

            // Suscribir token al tópico
            $messaging->subscribeToTopic($topic, [$token]);

            Log::info('Token suscrito al tópico correctamente', [
                'token' => substr($token, 0, 10) . '...',
                'topic' => $topic
            ]);

            return true;
        } catch (MessagingException $e) {
            Log::error('Error al suscribir token al tópico: ' . $e->getMessage(), [
                'token' => substr($token, 0, 10) . '...',
                'topic' => $topic,
                'error_code' => $e->getCode(),
                'error_details' => $e->getMessage()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Excepción al suscribir token al tópico: ' . $e->getMessage(), [
                'exception' => $e,
                'token' => substr($token, 0, 10) . '...',
                'topic' => $topic
            ]);
            return false;
        }
    }
}
