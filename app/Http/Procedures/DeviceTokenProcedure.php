<?php

namespace App\Http\Procedures;

use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\Permissions;
use Carbon\Carbon;
use Sajya\Server\Procedure;
use App\Attributes\RpcProcedure;
use App\Models\DeviceToken;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[RpcProcedure(version: 'v1', group: 'notifications')]
class DeviceTokenProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'device_token';

    /**
     * @inheritDoc
     */
    public function getMethodsPermissions(): array
    {
        return [
            'registerDeviceToken' => [Permissions::NORMAL],
            'unregisterDeviceToken' => [Permissions::NORMAL],
        ];
    }

    /**
     * Зарегистрировать токен устройства для push-уведомлений.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function registerDeviceToken(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $user = auth('sanctum')->user();

        if (!$user) {
            return $responseBuilder->setMessage('Пользователь не авторизован')->build();
        }

        // Проверяем, существует ли уже этот токен у данного пользователя
        $existingToken = DeviceToken::where('token', $data['token'])
                                    ->where('user_id', $user->id)
                                    ->first();

        if ($existingToken) {
            // Обновляем существующий токен
            $existingToken->update([
                'device_type' => $data['device_type'] ?? $existingToken->device_type,
                'device_name' => $data['device_name'] ?? $existingToken->device_name,
                'is_active' => true,
                'last_used_at' => Carbon::now(),
            ]);

            return $responseBuilder
                ->setData($existingToken)
                ->setMessage('Токен устройства обновлен')
                ->build();
        }

        // Создаем новый токен
        $deviceToken = DeviceToken::create([
            'user_id' => $user->id,
            'token' => $data['token'],
            'device_type' => $data['device_type'] ?? 'web',
            'device_name' => $data['device_name'] ?? null,
            'is_active' => true,
            'last_used_at' => Carbon::now(),
        ]);

        return $responseBuilder
            ->setData($deviceToken)
            ->setMessage('Токен устройства успешно зарегистрирован')
            ->build();
    }

    /**
     * Отменить регистрацию токена устройства.
     *
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function unregisterDeviceToken(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $user = auth('sanctum')->user();

        if (!$user) {
            return $responseBuilder->setMessage('Пользователь не авторизован')->build();
        }

        $token = $data['token'];

        // Деактивируем токен вместо удаления
        $deviceToken = DeviceToken::where('token', $token)
                                  ->where('user_id', $user->id)
                                  ->first();

        if ($deviceToken) {
            $deviceToken->update(['is_active' => false]);
            return $responseBuilder->setMessage('Токен устройства деактивирован')->build();
        }

        return $responseBuilder->setMessage('Токен устройства не найден')->build();
    }
}
