<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\NotificationPushService;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    protected NotificationPushService $pushService;

    public function __construct(NotificationPushService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Обработка события "создано уведомление"
     */
    public function created(Notification $notification): void
    {
        try {
            $this->pushService->sendPushForNotification($notification);
        } catch (\Exception $e) {
            Log::error("Error in NotificationObserver for notification #{$notification->id}: " . $e->getMessage());
        }
    }
}
