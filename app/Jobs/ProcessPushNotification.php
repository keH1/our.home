<?php

namespace App\Jobs;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\ApartmentRepository;
use App\Repositories\CounterRepository;
use App\Services\NotificationPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class ProcessPushNotification implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct(protected Notification $notification)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(NotificationPushService $notificationService): void
    {
        $notification = $this->notification;
        $notificationService->sendPushForNotification($notification);
    }
}
