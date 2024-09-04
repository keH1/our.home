<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Sajya\Server\Procedure;
use App\Models\User;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

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
     * @return array|JsonResponse
     */
    public function register(Request $request)
    {

        $params = json_decode($request->getContent(), true)['params'];
        $validator = Validator::make($params, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $pass = Hash::make($params['password']);
        $params['password'] = $pass;
        $user = new User($params);
        $user->password = $pass;
        $user->phone = $params['phone'];
        $user->save();

        $token = $user->createToken($params['password'])->plainTextToken;
        return ['token' => $token];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $user = User::where('phone', $request['phone'])->first();
        if (!$user || $request['password'] !== $user->password) {
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
        }

        return response()->json(['token' => $user->createToken('AuthToken')->plainTextToken]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getUserData(Request $request)
    {
        $userData = auth()->user()->with('client')->first();
        $apartments = $userData->client->apartments()->get();
        $response = [
            "name" => $userData->name,
            "email" => $userData->email,
            "phone" => $userData->phone,
        ];
        foreach ($apartments as $apartment) {
            $response['apartments'][] = $apartment['id'];
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if ($request['new_password'] == $user->password) {
            return ['warning' => 'Same password'];
        }
        if ($request['password'] != $user->password) {
            return ['warning' => 'Wrong password'];
        }
        $user->password = $request['new_password'];
        $user->save();
        return ['status' => 'Password was changed'];
    }


    /**
     * @param Request $request
     * @return string[]
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return ['status' => 'You are logged out'];
    }

    public function ping(Request $request)
    {
        return true;
    }
}
