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
        $account = AccountPersonalNumber::where('number', $accountNumber)->first();
        return Apartment::find($account->apartment_id);
    }
}
