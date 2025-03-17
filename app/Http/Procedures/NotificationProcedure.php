<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Attributes\RpcProcedure;
use App\Contracts\ProcedurePermissionsInterface;
use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Enums\Permissions;
use App\Models\Apartment;
use App\Models\Notification;
use App\Services\ApiResponseBuilder;
use App\Services\PaginationBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;

#[RpcProcedure(version: 'v1', group: 'notifications')]
class NotificationProcedure extends Procedure implements ProcedurePermissionsInterface
{
    public static string $name = 'notification';

    public function getMethodsPermissions(): array
    {
        return [
            'createAddressNotification' => [Permissions::NORMAL],
            'createUserNotification' => [Permissions::NORMAL],
            'createSystemNotification' => [Permissions::NORMAL],
            'removeAddressNotification' => [Permissions::NORMAL],
            'updateAddressNotification' => [Permissions::NORMAL],
            'getAddressNotifications' => [Permissions::NORMAL],
            'getNotifications' => [Permissions::NORMAL],
            'markAllNotificationsAsRead' => [Permissions::NORMAL],
        ];
    }

    /**
     * Создать пользовательское уведомление.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function createUserNotification(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $notificationCategories = array_column(NotificationCategory::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'text' => 'required|string',
            'title' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'category' => ['nullable', Rule::in($notificationCategories)],
            'action_type' => 'nullable|string',
            'action_value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $notification = new Notification();
        $notification->type = NotificationType::USER;
        $notification->title = $data['title'];
        $notification->text = $data['text'];
        $notification->category = $data->get('category', NotificationCategory::GENERAL);
        $notification->is_read = false;
        $notification->action_type = $data['action_type'] ?? null;
        $notification->action_value = $data['action_value'] ?? null;
        $notification->user_id = $data['user_id'];
        $notification->save();

        return $responseBuilder->setData($notification->toArray())->setMessage(
            'Пользовательское уведомление успешно создано'
        )->build();
    }

    /**
     * Создать системное уведомление.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function createSystemNotification(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $notificationCategories = array_column(NotificationCategory::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'text' => 'required|string',
            'title' => 'required|string',
            'category' => ['nullable', Rule::in($notificationCategories)],
            'action_type' => 'nullable|string',
            'action_value' => 'nullable|string',
            'date_to' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $notification = new Notification();
        $notification->type = NotificationType::SYSTEM;
        $notification->title = $data['title'];
        $notification->text = $data['text'];
        $notification->category = $data->get('category', NotificationCategory::GENERAL);
        $notification->is_read = false;
        $notification->action_type = $data['action_type'] ?? null;
        $notification->action_value = $data['action_value'] ?? null;

        if ($data->has('date_to')) {
            $notification->date_to = Carbon::parse($data['date_to']);
        }

        $notification->save();

        return $responseBuilder->setData($notification->toArray())
                               ->setMessage('Системное уведомление успешно создано')
                               ->build();
    }

    /**
     * Создать адресное уведомление.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function createAddressNotification(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $notificationCategories = array_column(NotificationCategory::cases(), 'value');

        $validator = Validator::make($data->toArray(), [
            'text' => 'required|string',
            'title' => 'required|string',
            'date_to' => 'required|date|after:now',
            'house_ids' => 'required|array|min:1',
            'house_ids.*' => 'integer|exists:houses,id',
            'category' => ['nullable', Rule::in($notificationCategories)],
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $notification = new Notification();
        $notification->type = NotificationType::ADDRESS;
        $notification->title = $data['title'];
        $notification->text = $data['text'];
        $notification->category = $data->get('category', NotificationCategory::GENERAL);
        $notification->is_read = false; // Значение по умолчанию
        $notification->date_to = Carbon::parse($data['date_to']);
        $notification->save();

        $houseIds = $data['house_ids'];
        $notification->houses()->sync($houseIds);

        return $responseBuilder->setData($notification->toArray())
                               ->setMessage('Адресное уведомление успешно создано')
                               ->build();
    }

    /**
     * Удалить адресное уведомление.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function removeAddressNotification(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $validator = Validator::make($data->toArray(), [
            'id' => 'required|integer|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $notification = Notification::find($data['id']);
        if ($notification->type !== NotificationType::ADDRESS) {
            throw new InvalidParams('Уведомление не является адресным и не может быть удалено этим методом.');
        }

        $notification->delete();

        return $responseBuilder->setMessage('Адресное уведомление успешно удалено')->build();
    }

    /**
     * Обновить адресное уведомление.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function updateAddressNotification(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params']);
        $notificationCategories = array_column(NotificationCategory::cases(), 'value');

        // Валидация входных данных
        $validator = Validator::make($data->toArray(), [
            'id' => 'required|integer|exists:notifications,id',
            'text' => 'sometimes|string',
            'date_to' => 'sometimes|date|after:now',
            'house_ids' => 'sometimes|array|min:1',
            'house_ids.*' => 'integer|exists:houses,id',
            'category' => ['sometimes', Rule::in($notificationCategories)],
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $notification = Notification::find($data['id']);
        if ($notification->type !== NotificationType::ADDRESS) {
            throw new InvalidParams('Уведомление не является адресным и не может быть отредактировано этим методом.');
        }

        if ($data->has('text')) {
            $notification->text = $data['text'];
        }
        if ($data->has('date_to')) {
            $notification->date_to = Carbon::parse($data['date_to']);
        }
        if ($data->has('category')) {
            $notification->category = $data['category'];
        }

        $notification->save();

        if ($data->has('house_ids')) {
            $houseIds = $data['house_ids'];
            $notification->houses()->sync($houseIds);
        }

        return $responseBuilder->setData($notification->toArray())
                               ->setMessage('Адресное уведомление успешно обновлено')
                               ->build();
    }

    /**
     * Получить адресные уведомления.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     * @throws InvalidParams
     */
    public function getAddressNotifications(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params'] ?? []);
        $validator = Validator::make($data->toArray(), [
            'is_crm' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->first());
        }

        $isCrm = $data->get('is_crm', false);
        if ($isCrm) {
            $notifications = Notification::where('type', NotificationType::ADDRESS)->with('houses')->get();

            $notificationsData = $notifications->map(function ($notification) {
                $addresses = $notification->houses->map(function ($house) {
                    return [
                        'house_id' => $house->id,
                        'city' => $house->city,
                        'street' => $house->street,
                        'number' => $house->number,
                        'building' => $house->building,
                    ];
                });

                return array_merge($notification->toArray(), ['addresses' => $addresses]);
            });

            return $responseBuilder->setData($notificationsData->toArray())
                                   ->setMessage('Все адресные уведомления')
                                   ->build();
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            throw new InvalidParams('Пользователь не авторизован');
        }

        $clients = $user->clients;
        if ($clients->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('У пользователя нет связанных клиентов')->build();
        }

        $clientIds = $clients->pluck('id');
        $apartments = Apartment::whereHas('clients', function ($query) use ($clientIds) {
            $query->whereIn('clients.id', $clientIds);
        })->get();

        if ($apartments->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('У клиентов нет связанных квартир')->build();
        }

        $houseIds = $apartments->pluck('house_id')->unique();
        if ($houseIds->isEmpty()) {
            return $responseBuilder->setData([])->setMessage('У квартир нет связанных домов')->build();
        }

        $notifications = Notification::where('type', NotificationType::ADDRESS)->whereHas(
            'houses',
            function ($query) use ($houseIds) {
            $query->whereIn('houses.id', $houseIds);
        }
        )->with('houses')->get();

        $notificationsData = $notifications->map(function ($notification) {
            $addresses = $notification->houses->map(function ($house) {
                return [
                    'house_id' => $house->id,
                    'city' => $house->city,
                    'street' => $house->street,
                    'number' => $house->number,
                    'building' => $house->building,
                ];
            });

            return array_merge($notification->toArray(), ['addresses' => $addresses]);
        });

        return $responseBuilder->setData($notificationsData->toArray())->setMessage(
            'Адресные уведомления для пользователя'
        )->build();
    }

    /**
     * Получить список уведомлений.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     */
    public function getNotifications(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $data = collect(json_decode($request->getContent(), true)['params'] ?? []);
        $paginationBuilder = PaginationBuilder::fromRequest($request);

        $validator = Validator::make($data->toArray(), [
            'for_user' => 'nullable|boolean',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams($validator->errors()->toArray());
        }

        $query = Notification::query();

        // Получение уведомлений только для текущего пользователя
        if ($data->get('for_user', false)) {
            $user = auth('sanctum')->user();

            if (!$user) {
                return $responseBuilder->setMessage('Пользователь не авторизован')->build();
            }

            $query->where(function ($q) use ($user) {
                // Прямые уведомления пользователю
                $q->where('user_id', $user->id)
                    // ИЛИ системные уведомления
                  ->orWhere('type', NotificationType::SYSTEM->value);

                // ИЛИ адресные уведомления для домов пользователя
                $userHouseIds = $user->clients()->with('apartments.house')->get()->flatMap(function ($client) {
                    return $client->apartments->pluck('house_id');
                })->unique()->values()->toArray();

                if (!empty($userHouseIds)) {
                    $q->orWhereHas('houses', function ($query) use ($userHouseIds) {
                        $query->whereIn('houses.id', $userHouseIds);
                    });
                }
            });
        }

        // Фильтр по типу уведомления
        if ($data->has('type')) {
            $query->where('type', $data['type']);
        }

        // Фильтр по категории
        if ($data->has('category')) {
            $query->where('category', $data['category']);
        }

        // Фильтр по статусу прочитано/не прочитано
        if ($data->has('is_read')) {
            $query->where('is_read', $data['is_read']);
        }

        // Учет срока действия уведомления
        $query->where(function ($q) {
            $q->whereNull('date_to')->orWhere('date_to', '>=', Carbon::now());
        });


        $query->orderBy('created_at', 'desc');
        if ($paginationBuilder->isPaginationEnabled()) {
            $total = $query->count();
            $paginationBuilder->setTotal($total);

            $notifications = $query->limit($paginationBuilder->getLimit())
                                   ->offset($paginationBuilder->getOffset() ?? 0)
                                   ->with('houses')  // Загружаем связанные дома для адресных уведомлений
                                   ->get();

            return $responseBuilder->setData($notifications)->setPagination($paginationBuilder)->build();
        }


        $notifications = $query->with('houses')->get();

        return $responseBuilder->setData($notifications)->build();
    }

    /**
     * Отметить все уведомления текущего пользователя как прочитанные.
     *
     * @param  Request  $request
     * @param  ApiResponseBuilder  $responseBuilder
     * @return array
     */
    public function markAllNotificationsAsRead(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            throw new InvalidParams('Пользователь не авторизован');
        }

        // Получаем ID всех домов пользователя
        $userHouseIds = $user->clients()->with('apartments.house')->get()->flatMap(function ($client) {
                return $client->apartments->pluck('house_id');
            })->unique()->values()->toArray();

        // Формируем запрос для получения всех уведомлений пользователя
        $query = Notification::where('is_read', false)->where(function ($q) use ($user, $userHouseIds) {
                // Уведомления, адресованные непосредственно пользователю
                $q->where('user_id', $user->id);

                // Системные уведомления
                $q->orWhere('type', NotificationType::SYSTEM->value);

                // Адресные уведомления для домов пользователя
                if (!empty($userHouseIds)) {
                    $q->orWhereHas('houses', function ($query) use ($userHouseIds) {
                        $query->whereIn('houses.id', $userHouseIds);
                    });
                }
            })
            // Учитываем только актуальные уведомления
                             ->where(function ($q) {
                $q->whereNull('date_to')->orWhere('date_to', '>=', Carbon::now());
            });

        // Получаем количество обновленных уведомлений
        $count = $query->count();

        // Отмечаем все уведомления как прочитанные
        $query->update(['is_read' => true]);

        return $responseBuilder->setData(['count' => $count])->setMessage(
                $count > 0 ? "Отмечено {$count} уведомлений как прочитанные" : "Непрочитанных уведомлений не найдено"
            )->build();
    }
}
