<?php

namespace App\Repositories;

use App\Models\Apartment;

class ApartmentRepository {
    /**
     * @param $accountID
     * @return Apartment|null
     */
    public function findApartmentByAccountID($accountID): Apartment|null
    {
        return Apartment::where('personal_number', $accountID)->first();
    }

}
