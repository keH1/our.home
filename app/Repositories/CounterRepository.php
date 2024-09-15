<?php

namespace App\Repositories;


use App\Enums\CounterType;
use Illuminate\Support\Str;

class CounterRepository
{
    /**
     * @param string $type
     * @return bool
     */
    public function checkCounterType(string $type): bool
    {
        foreach (CounterType::cases() as $case) {
            if (Str::contains($type, $case->counterTypes())) {
                return true;
            }
        }
        return false;
    }
}
