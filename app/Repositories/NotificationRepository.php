<?php

namespace App\Repositories;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Jobs\ProcessPushNotification;
use App\Models\Notification;
use App\Models\NotificationTemplate;

class NotificationRepository
{

    /**
     * @param Notification $notification
     * @return void
     */
    public function putMessageIntoQueue(Notification $notification): void
    {
        ProcessPushNotification::dispatch($notification);
    }

    /**
     * @param int $userID
     * @param NotificationType $notificationType
     * @param array $additionalParams
     * @return Notification|void|null
     */
    public function createNotificationByTemplate(int $userID, NotificationType $notificationType, array $additionalParams = [])
    {
        $notificationTemplate = NotificationTemplate::where('type', $notificationType)->first();
        if ($notificationTemplate) {
            $notification = new Notification();
            if (isset($additionalParams['category']) && strlen($additionalParams['category']) > 0) {
                $notification->category = NotificationCategory::{strtoupper($additionalParams['category'])}?->value;
            }
            if (isset($additionalParams['action_type']) && strlen($additionalParams['action_type']) > 0) {
                $notification->action_type = $additionalParams['action_type'];
            }
            $notification->type = $notificationType;
            $notification->title = $notificationTemplate->title;
            $notification->text = $notificationTemplate->text;
            $notification->template_id = $notificationTemplate->id;
            $notification->is_read = false;
            $notification->user_id = $userID;
            if ($notification->save()) {
                return $notification;
            }else{
                Throw new \Exception('Error saving notification');
            }
        } else {
            return null;
        }
    }
}
