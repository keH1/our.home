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

    /**
     * @param int $apartmentId
     * @return Apartment|null
     */
    public function findApartmentById(int $apartmentId): ?Apartment
    {
        return Apartment::with(['house', 'account.clients'])->find($apartmentId);
    }

}
