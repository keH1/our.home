<?php

namespace App\Repositories;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;


class ApartmentRepository
{
        /**
     * @param int $apartmentId
     * @return Apartment|null
     */
    public function findApartmentById(int $apartmentId): ?Apartment
    {
        return Apartment::with(['house', 'account.clients'])->find($apartmentId);
    }


    /**
     * @param mixed $accountNumber
     * @return Apartment|null
     */
    public function findApartmentByAccountNumber(mixed $accountNumber): ?Apartment
    {
        $account = AccountPersonalNumber::where('union_number', $accountNumber)->first();
        if ($account) {
            return Apartment::find($account->apartment_id);
        }

        return Apartment::where('gis_id', $accountNumber)->first();
    }
}
