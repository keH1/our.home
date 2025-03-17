<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use App\Notifications\FcmPushNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\CloudMessage;

class PushNotificationService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Отправить уведомление одному пользователю.
     *
     * @param  User  $user
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $imageUrl
     * @return bool
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        try {
            $user->notify(new FcmPushNotification($title, $body, $data, $imageUrl));

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка отправки FCM уведомления пользователю: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отправить уведомление нескольким пользователям.
     *
     * @param  array  $users
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $imageUrl
     * @return bool
     */
    public function sendToUsers(
        array $users,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        try {
            Notification::send($users, new FcmPushNotification($title, $body, $data, $imageUrl));

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка массовой отправки FCM: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отправить уведомление по конкретным токенам.
     *
     * @param  array  $tokens
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $imageUrl
     * @return bool
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        if (empty($tokens)) {
            return false;
        }

        try {
            $message = CloudMessage::new()->withNotification([
                    'title' => $title,
                    'body' => $body,
                    'image' => $imageUrl,
                ])->withData($data);

            $response = $this->messaging->sendMulticast($message, $tokens);

            // Обрабатываем результаты
            $successCount = $response->successes()->count();
            $failureCount = $response->failures()->count();

            // Обрабатываем неудачные отправки
            if ($failureCount > 0) {
                foreach ($response->failures()->getItems() as $failure) {
                    $token = $failure->target()->value();
                    $error = $failure->error()->getMessage();

                    // Деактивируем недействительные токены
                    if ($failure->error() instanceof InvalidArgumentException || str_contains(
                            $error,
                            'NOT_FOUND'
                        ) || str_contains($error, 'INVALID_ARGUMENT') || str_contains($error, 'UNREGISTERED')) {
                        DeviceToken::where('token', $token)->update(['is_active' => false]);

                        Log::warning("FCM token деактивирован: {$token}. Причина: {$error}");
                    } else {
                        Log::error("Ошибка отправки FCM: {$error} для токена {$token}");
                    }
                }
            }

            Log::info("FCM отправлено: успешно {$successCount}, неудачно {$failureCount}");

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error('Ошибка отправки FCM: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отправить уведомление на тему (topic).
     *
     * @param  string  $topic
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @param  string|null  $imageUrl
     * @return bool
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        ?string $imageUrl = null
    ): bool {
        try {
            $message = CloudMessage::withTarget('topic', $topic)->withNotification([
                    'title' => $title,
                    'body' => $body,
                    'image' => $imageUrl,
                ])->withData($data);

            $this->messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка отправки FCM в тему: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Подписать токены на тему.
     *
     * @param  array  $tokens
     * @param  string  $topic
     * @return bool
     */
    public function subscribeToTopic(array $tokens, string $topic): bool
    {
        try {
            $this->messaging->subscribeToTopic($topic, $tokens);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка подписки на тему: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Отписать токены от темы.
     *
     * @param  array  $tokens
     * @param  string  $topic
     * @return bool
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка отписки от темы: '.$e->getMessage());

            return false;
        }
    }
}
