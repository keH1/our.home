<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\FcmPushNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class UserPushStrategy implements PushNotificationStrategy
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Отправить пуш-уведомление пользователю.
     *
     * @param Notification $notification
     * @return bool
     */
    public function send(Notification $notification): bool
    {
        if (!$notification->user_id) {
            Log::warning("No user_id specified for USER notification #{$notification->id}");
            return false;
        }

        $user = User::find($notification->user_id);
        if (!$user) {
            Log::warning("User #{$notification->user_id} not found for notification #{$notification->id}");
            return false;
        }

        try {
            // Используем существующий сервис для отправки уведомления
            $result = $this->pushService->sendToUser(
                $user,
                $notification->title,
                $notification->text,
                $this->prepareData($notification)
            );

            if ($result) {
                Log::info("USER push notification sent for notification #{$notification->id} to user #{$user->id}");
            } else {
                Log::info("No push notification sent for notification #{$notification->id} to user #{$user->id} (possibly no active device tokens)");
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error sending USER push notification for notification #{$notification->id}: " . $e->getMessage());
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
