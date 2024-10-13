<?php

namespace App\Repositories;


use App\Enums\CounterType;
use App\Models\Claim;
use App\Models\PaidService;
use Illuminate\Support\Str;
use Sajya\Server\Exceptions\InvalidParams;
use Illuminate\Support\Collection;

class ClaimRepository
{
    /**
     * @param Collection $data
     * @param array|null $files
     * @return Claim
     */
    public function createClaim(Collection $data, array $files = null): Claim
    {
        $paidServiceID = $data['id'];
        $message = $data['message'];
        $claim = new Claim();
        if (PaidService::query()->find($paidServiceID) == null) {
            throw new InvalidParams(['message' => 'paid service id not found']);
        }
        $claim->paid_service_id = $paidServiceID;
        $claim->comment = $message;
        $claim->category_id = null;
        $claim->user_id = auth()->user()->id;
        $claim->type = "paid";
        $claim->save();
        if ($files !== null) {
            $claim->files()->saveMany($files);
        }

        return $claim;
    }

}
