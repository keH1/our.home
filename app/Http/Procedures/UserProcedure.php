<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\User;
use App\Services\ApiResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Procedure;


class UserProcedure extends Procedure
{
    /**
     * The name of the procedure that is used for referencing.
     *
     * @var string
     */
    public static string $name = 'user_procedure';


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array
     */
    public function register(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $params = json_decode($request->getContent(), true)['params'];
        $validatorRules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string']
        ];
        $validator = Validator::make($params, $validatorRules);

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $pass = Hash::make($params['password']);
        $user = new User($params);
        $user->password = $pass;
        $user->save();
        $token = $user->createToken($params['password'])->plainTextToken;

        return $responseBuilder->setData(['token' => $token])->build();
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array|string[]
     */
    public function login(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $user = User::where('phone', $request['phone'])->first();
        if (!$user || !Hash::check($request['password'], $user->password)) {
            return ['error' => 'The provided credentials are incorrect.'];
        }

        return $responseBuilder->setData(['token' => $user->createToken('AuthToken')->plainTextToken])->build();
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return array|string[]
     */
    public function getUserData(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $userData = auth('sanctum')->user();
        $clients = $userData->clients;

        $response = [
            'id' => $userData->id,
            'email' => $userData->email,
            'phone' => $userData->phone,
            'clients' => $clients->map(function ($client) {
                return [
                    'id'=>$client->id,
                    'name'=>$client->name,
                    'debt' => $client->debt,
                    'accounts' => $client->accounts->map(function ($account) {
                        return [
                            'id' => $account->id,
                            'number' => $account->number,
                            'union_number' => $account->union_number,
                            'apartment_id' => $account->apartment_id,
                        ];
                    })
                ];
            })
        ];

        return $responseBuilder->setData($response)->build();
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return string[]
     */
    public function changePassword(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        $user = auth('sanctum')->user();

        if (Hash::check($request['new_password'], $user->password)) {
            return ['warning' => 'Same password'];
        }
        if (!Hash::check($request['password'], $user->password)) {
            return ['warning' => 'Same password'];
        }
        $user->password = $request['new_password'];
        $user->save();
        return $responseBuilder->setData([])->setMessage('Password was changed')->build();
    }


    /**
     * @param Request $request
     * @param ApiResponseBuilder $responseBuilder
     * @return string[]
     */
    public function logout(Request $request, ApiResponseBuilder $responseBuilder): array
    {
        auth('sanctum')->user()->tokens()->delete();
        return $responseBuilder->setData([])->setMessage('You are logged out')->build();
    }

    /**
     * @param Request $request
     * @return true
     */
    public function ping(Request $request): true
    {
        return true;
    }
}
