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
        return Apartment::with(['house', 'accounts'])->find($apartmentId);
    }


    /**
     * @param mixed $accountNumber
     * @return Apartment|null
     */
    public function findApartmentByAccountNumber(mixed $accountNumber): ?Apartment
    {
        $account = AccountPersonalNumber::where('number', $accountNumber)->first();
        if ($account) {
            return Apartment::find($account->apartment_id);
        }

        return null;
    }

    /**
     * @param mixed $apartment
     * @return Apartment|null
     */
    public function checkByOneCID(mixed $apartment): Apartment|null
    {
        return Apartment::where('one_c_id',$apartment['id'])->first();
    }
}
