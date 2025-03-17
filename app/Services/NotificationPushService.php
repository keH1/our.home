<?php

namespace App\Services;

use App\Factories\PushNotificationStrategyFactory;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationPushService
{
    protected PushNotificationStrategyFactory $strategyFactory;

    public function __construct(PushNotificationStrategyFactory $strategyFactory)
    {
        $this->strategyFactory = $strategyFactory;
    }

    /**
     * Отправить пуш-уведомление на основе созданного уведомления.
     *
     * @param Notification $notification
     * @return bool
     */
    public function sendPushForNotification(Notification $notification): bool
    {
        try {
            // Получаем соответствующую стратегию для типа уведомления
            $strategy = $this->strategyFactory->create($notification->type);

            // Отправляем уведомление с помощью выбранной стратегии
            return $strategy->send($notification);
        } catch (\Exception $e) {
            Log::error("Error in NotificationPushService for notification #{$notification->id}: " . $e->getMessage());
            return false;
        }
    }
}
