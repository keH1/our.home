<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\AccountPersonalNumber;
use App\Models\CounterData;
use App\Models\CounterHistory;
use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Console\Command;

class TestimonySubmission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testimony-submission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending push notification after 20 numbers of month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationRepository = new NotificationRepository();
        // смотрим клиентов которые не подали  показания
        $existCounterHistories = CounterHistory::whereMonth('created_at', now()->month)
            ->where('from_1c',false)
            ->where('from_1c',false)
            ->get();

        $apartments = CounterData::with('apartment')
            ->whereNotIn('id', $existCounterHistories->pluck('counter_name_id'))
            ->where('apartment_id','>',0)
            ->get()->map(function ($item) {
                return $item->apartment;
            });
        $clients = [];
        $apartments->map(function ($item) use (&$clients) {
            foreach ($item->clients as $client) {
                $clients[$client->id] = $client;
            }
        });
        // начинаем пихать в очередь сообщения чтобы джоба подхватила и начала отправлять
        foreach ($clients as $client) {
            $user = $client->user;
            //тут нужно бы создать сообщение по темплейту
            $notification = $notificationRepository->createNotificationByTemplate($user->id,NotificationType::TESTIMONY_SUBMISSION);
            $notificationRepository->putMessageIntoQueue($notification);
        }

    }
}
