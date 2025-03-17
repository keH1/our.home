<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class SystemPushStrategy implements PushNotificationStrategy
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Отправить системное пуш-уведомление.
     *
     * @param  Notification  $notification
     * @return bool
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function send(Notification $notification): bool
    {
        try {
            $data = $this->prepareData($notification);

            // Для системных уведомлений лучше юзать топики
            // Например, можно отправить на определенную тему (topic)
            $topic = 'system_notifications';

            // Используем сервис для отправки уведомления в тему
            $result = $this->pushService->sendToTopic(
                $topic,
                $notification->title,
                $notification->text,
                $data
            );

            if ($result) {
                Log::info("SYSTEM push notification sent to topic '{$topic}' for notification #{$notification->id}");
            } else {
                Log::warning("Failed to send SYSTEM push notification to topic '{$topic}' for notification #{$notification->id}");
            }

            return $result;

            // Альтернативный вариант - если нужно отправить всем пользователям:
            /*
            $users = User::where('is_active', true)->get();

            if ($users->isEmpty()) {
                Log::info("No active users found for SYSTEM notification #{$notification->id}");
                return false;
            }

            // Используем сервис для отправки уведомлений всем пользователям
            $result = $this->pushService->sendToUsers(
                $users->all(),
                $notification->title,
                $notification->text,
                $data
            );

            if ($result) {
                Log::info("SYSTEM push notification sent for notification #{$notification->id} to {$users->count()} users");
            } else {
                Log::info("No SYSTEM push notification sent for notification #{$notification->id} (possibly no users with active tokens)");
            }

            return $result;
            */
        } catch (\Exception $e) {
            Log::error("Error sending SYSTEM push notification for notification #{$notification->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Подготовить данные для пуш-уведомления.
     *
     * @param Notification $notification
     * @return array
     */
    protected function prepareData(Notification $notification): array
    {
        return [
            'notification_id' => $notification->id,
            'type' => $notification->type->value,
            'action_type' => $notification->action_type,
            'action_value' => $notification->action_value,
            'category' => $notification->category
        ];
    }
}
