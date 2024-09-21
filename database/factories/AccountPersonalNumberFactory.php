<?php

namespace Database\Factories;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountPersonalNumberFactory extends Factory
{
    protected $model = AccountPersonalNumber::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('APN#####'),
            'apartment_id' => null, // Мы установим это поле позже, если потребуется
        ];
    }
}
