<?php

namespace App\Factories;

use App\Enums\NotificationType;
use App\Strategies\PushNotification\AddressPushStrategy;
use App\Strategies\PushNotification\NonePushStrategy;
use App\Strategies\PushNotification\PushNotificationStrategy;
use App\Strategies\PushNotification\SystemPushStrategy;
use App\Strategies\PushNotification\TestimonySubmissionStrategy;
use App\Strategies\PushNotification\UserPushStrategy;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PushNotificationStrategyFactory
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Создать стратегию отправки пуш-уведомлений на основе типа уведомления.
     *
     * @param  NotificationType  $notificationType
     * @return PushNotificationStrategy
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function create(NotificationType $notificationType): PushNotificationStrategy
    {
        return match ($notificationType) {
            NotificationType::NONE => $this->container->make(NonePushStrategy::class),
            NotificationType::ADDRESS => $this->container->make(AddressPushStrategy::class),
            NotificationType::SYSTEM => $this->container->make(SystemPushStrategy::class),
            NotificationType::USER => $this->container->make(UserPushStrategy::class),
            NotificationType::TESTIMONY_SUBMISSION => $this->container->make(TestimonySubmissionStrategy::class),
            default => throw new InvalidArgumentException("Unsupported notification type: {$notificationType->value}"),
        };
    }
}
