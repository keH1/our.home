<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Models\Apartment;
use App\Models\Notification;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Procedure;

class NotificationProcedure extends Procedure
{
    public static string $name = 'notification';

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
            $notifications = Notification::where('type', NotificationType::ADDRESS)->get();

            return $responseBuilder->setData($notifications->toArray())->setMessage('Все адресные уведомления')->build(
                );
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
            )->get();

        return $responseBuilder->setData($notifications)->setMessage('Адресные уведомления для пользователя')->build();
    }
}
