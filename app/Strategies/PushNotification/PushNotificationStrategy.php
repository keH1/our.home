<?php

namespace App\Strategies\PushNotification;

use App\Models\Notification;

interface PushNotificationStrategy
{
    /**
     * Отправить пуш-уведомление на основе созданного уведомления.
     *
     * @param Notification $notification
     * @return bool
     */
    public function send(Notification $notification): bool;
}
