<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class TestimonySubmissionStrategy implements PushNotificationStrategy
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
        try {
            $data = $this->prepareData($notification);

            $result = $this->pushService->sendToUser(
                $notification->user,
                $notification->title,
                $notification->text,
                $data
            );

            if ($result) {
                Log::info("Testimony submission push notification sent to user '{$notification->user}' for notification #{$notification->id}");
            } else {
                Log::warning("Failed to send Testimony submission push notification to topic '{$notification->user}' for notification #{$notification->id}");
            }
            return $result;
        } catch (\Exception $e) {
            Log::error("Error sending Testimony submission push notification for notification #{$notification->id}: " . $e->getMessage());
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
