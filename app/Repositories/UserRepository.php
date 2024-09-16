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
     * @param $BIO
     * @return User
     */
    public function checkUserByBIO($BIO)
    {
        return User::where('name', $BIO)->first();
    }
    /**
     * @param $BIO
     * @return User
     */
    public function checkUserByAccountNumber($accountNumber)
    {
        return User::where('name', $BIO)->first();
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
