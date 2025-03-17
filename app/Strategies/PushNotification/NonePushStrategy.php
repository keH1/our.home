<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class NonePushStrategy implements PushNotificationStrategy
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Для типа NONE не отправляем пуш-уведомления.
     *
     * @param  Notification  $notification
     * @return bool
     */
    public function send(Notification $notification): bool
    {
        Log::info("NONE type notification #{$notification->id}, no push notification sent");

        return true;
    }
}
