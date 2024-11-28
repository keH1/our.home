<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Client;

class UserDataController extends Controller
{
    /**
     * Получение данных пользователя по номеру телефона.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserDataByPhone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $client = Client::with(['apartments.house'])->where('phone', $validated['phone'])->first();

        if (!$client) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $apartments = $client->apartments->map(function ($apartment) {
            $house = $apartment->house;
            return sprintf(
                '%s, %s, %s, %s, %s',
                $house->city ?? 'Город не указан',
                $house->street ?? 'Улица не указана',
                $house->number ?? 'Дом не указан',
                $house->building ?? 'Строение не указано',
                $apartment->number ?? 'Квартира не указана'
            );
        })->toArray();

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'phone' => $client->phone,
            'email' => $client->email ?? 'Email не указан',
            'debt' => $client->debt ?? '0',
            'apartments' => $apartments,
        ]);
    }
}
