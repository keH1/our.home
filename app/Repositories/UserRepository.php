<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{

    /**
     * @param $customerPhone
     * @return User|null
     */
    public function checkUserByPhone($customerPhone): User|null
    {
        return User::where('phone', $customerPhone)->first();
    }

    /**
     * @param $email
     * @return User|null
     */
    public function checkUserByEmail($email): User|null
    {
        return User::where('name', $email)->first();
    }


}
