<?php

namespace App\Http\Procedures;

use App\Contracts\ProcedurePermissionsInterface;
use Sajya\Server\Procedure;
use App\Attributes\RpcProcedure;
use App\Enums\Permissions;
use App\Models\User;
use App\Services\ApiResponseBuilder;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

#[RpcProcedure(version: 'v1', group: 'notifications')]
class PushNotificationProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'push_notification';

    public function getMethodsPermissions(): array
    {
        return [
            'sendToUser' => [Permissions::NORMAL],
            'sendToTopic' => [Permissions::NORMAL],
            'subscribeToTopic' => [Permissions::NORMAL],
            'unsubscribeFromTopic' => [Permissions::NORMAL],
        ];
    }

    /**
     * Отправить уведомление пользователю.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PushNotificationService $pushService
     * @return array
     */
    public function sendToUser(Request $request, ApiResponseBuilder $responseBuilder, PushNotificationService $pushService): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $user = User::find($data['user_id']);
        if (!$user) {
            return $responseBuilder->setMessage('Пользователь не найден')->build();
        }

        $result = $pushService->sendToUser(
            $user,
            $data['title'],
            $data['body'],
            $data['data'] ?? [],
            $data['image_url'] ?? null
        );

        return $responseBuilder
            ->setData(['success' => $result])
            ->setMessage($result ? 'Уведомление отправлено успешно' : 'Не удалось отправить уведомление')
            ->build();
    }

    /**
     * Отправить уведомление на тему.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @param  PushNotificationService  $pushService
     * @return array
     * @throws \Kreait\Firebase\Exception\FirebaseException
     * @throws \Kreait\Firebase\Exception\MessagingException
     */
    public function sendToTopic(Request $request, ApiResponseBuilder $responseBuilder, PushNotificationService $pushService): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $result = $pushService->sendToTopic(
            $data['topic'],
            $data['title'],
            $data['body'],
            $data['data'] ?? [],
            $data['image_url'] ?? null
        );

        return $responseBuilder
            ->setData(['success' => $result])
            ->setMessage($result ? 'Уведомление отправлено успешно' : 'Не удалось отправить уведомление')
            ->build();
    }

    /**
     * Подписать токены на тему.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PushNotificationService $pushService
     * @return array
     */
    public function subscribeToTopic(Request $request, ApiResponseBuilder $responseBuilder, PushNotificationService $pushService): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $result = $pushService->subscribeToTopic(
            $data['tokens'],
            $data['topic']
        );

        return $responseBuilder
            ->setData(['success' => $result])
            ->setMessage($result ? 'Токены успешно подписаны на тему' : 'Не удалось подписать токены на тему')
            ->build();
    }

    /**
     * Отписать токены от темы.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @param PushNotificationService $pushService
     * @return array
     */
    public function unsubscribeFromTopic(Request $request, ApiResponseBuilder $responseBuilder, PushNotificationService $pushService): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);

        $result = $pushService->unsubscribeFromTopic(
            $data['tokens'],
            $data['topic']
        );

        return $responseBuilder
            ->setData(['success' => $result])
            ->setMessage($result ? 'Токены успешно отписаны от темы' : 'Не удалось отписать токены от темы')
            ->build();
    }
}
