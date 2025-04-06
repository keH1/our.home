<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $user = User::with('accounts')->where('phone', $validated['phone'])->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $apartments = $user->accounts->map(function ($account) {
            $house = $account->apartment->house;
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
            'user_id' => $user->id,
            'accounts' => $user->accounts,
            'apartments' => $apartments,
        ]);
    }
}
