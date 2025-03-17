<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\FcmPushNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class AddressPushStrategy implements PushNotificationStrategy
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Отправить пуш-уведомление на основе адресного уведомления.
     *
     * @param  Notification  $notification
     * @return bool
     */
    public function send(Notification $notification): bool
    {
        // Получаем все дома, связанные с уведомлением, и сразу загружаем пользователей
        $houses = $notification->houses()->with('apartments.clients.user')->get();

        if ($houses->isEmpty()) {
            Log::warning("No houses associated with ADDRESS notification #{$notification->id}");

            return false;
        }

        // Используем коллекции для эффективного извлечения пользователей
        $users = $houses->flatMap(function ($house) {
            return $house->apartments->flatMap(function ($apartment) {
                return $apartment->clients->map(function ($client) {
                    return $client->user;
                });
            });
        })->filter()->unique('id');

        if ($users->isEmpty()) {
            Log::info("No users found for ADDRESS notification #{$notification->id}");

            return false;
        }

        try {
            // Подготавливаем данные для пуш-уведомления
            $data = $this->prepareData($notification);

            // Используем сервис для отправки уведомлений всем пользователям
            $result = $this->pushService->sendToUsers(
                $users->all(),
                $notification->title,
                $notification->text,
                $data
            );

            if ($result) {
                Log::info(
                    "ADDRESS push notification sent for notification #{$notification->id} to {$users->count()} users"
                );
            } else {
                Log::info(
                    "No ADDRESS push notification sent for notification #{$notification->id} (possibly no users with active tokens)"
                );
            }

            return $result;
        } catch (\Exception $e) {
            Log::error(
                "Error sending ADDRESS push notification for notification #{$notification->id}: ".$e->getMessage()
            );

            return false;
        }
    }

    /**
     * Подготовить данные для пуш-уведомления.
     *
     * @param  Notification  $notification
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
