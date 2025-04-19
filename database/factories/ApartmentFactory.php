<?php

namespace Database\Factories;

use App\Models\AccountPersonalNumber;
use App\Models\Apartment;
use App\Models\House;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('APT###'),
            'account_number' => $this->faker->unique()->numerify('AN####'),
            'gis_id' => $this->faker->unique()->numerify('GKU####'),
            'account_owner' => $this->faker->name(),
            'house_id' => House::factory(),
        ];
    }
}
