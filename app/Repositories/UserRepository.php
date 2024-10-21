<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    /**
     * @param $customerPhone
     * @return User
     */
    public function checkUserByPhone($customerPhone)
    {
        return User::where('phone', $customerPhone)->first();
    }

    /**
     * @param $email
     * @return User
     */
    public function checkUserByEmail($email): User|null
    {
        return User::where('name', $email)->first();
    }


}
