<?php

namespace App\Repositories;

use App\Models\AccountPersonalNumber;

class AccountRepository {
    /**
     * @param $accountNumber
     * @return mixed
     */
    public function checkAccountByNumber($accountNumber): mixed
    {
        return AccountPersonalNumber::where('number', $accountNumber)->first();
    }
}
