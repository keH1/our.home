<?php

declare(strict_types=1);

namespace App\Http\Procedures;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sajya\Server\Procedure;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


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
     * @return array
     */
    public function register(Request $request): array
    {
        $params = json_decode($request->getContent(), true)['params'];
        $validatorRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string']
        ];
        foreach ($params as $key => $param){
            if(!in_array($key,$validatorRules)){
                return ['error' => 'Contain incorrect fields'];
            }
        }
        $validator = Validator::make($params, $validatorRules);

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $pass = Hash::make($params['password']);
        $user = new User($params);
        $user->password = $pass;
        $user->save();
        $token = $user->createToken($params['password'])->plainTextToken;

        return ['token' => $token];
    }


    /**
     * @param Request $request
     * @return array|string[]
     */
    public function login(Request $request): array
    {
        $user = User::where('phone', $request['phone'])->first();
        if (!$user || !Hash::check($request['password'], $user->password)) {
            return ['error' => 'The provided credentials are incorrect.'];
        }

        return ['token' => $user->createToken('AuthToken')->plainTextToken];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getUserData(Request $request): array
    {
        $userData = auth()->user()->with('client')->first();
        $apartments = $userData->client?->apartments()->get();
        if($apartments == null) return ['warning'=>"The client doesn't have an apartment yet"];
        $response = [
            "name" => $userData->name,
            "email" => $userData->email,
            "phone" => $userData->phone,
        ];
        $response['apartments'] = $apartments->pluck('id');

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function changePassword(Request $request): array
    {
        $user = auth()->user();

        if (Hash::check($request['new_password'], $user->password)) {
            return ['warning' => 'Same password'];
        }
        if (!Hash::check($request['password'], $user->password)) {
            return ['warning' => 'Same password'];
        }
        $user->password = $request['new_password'];
        $user->save();

        return ['status' => 'Password was changed'];
    }


    /**
     * @param Request $request
     * @return string[]
     */
    public function logout(Request $request): array
    {
        auth()->user()->tokens()->delete();

        return ['status' => 'You are logged out'];
    }

    public function ping(Request $request): true
    {
        return true;
    }
}
