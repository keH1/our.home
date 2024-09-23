<?php

namespace Database\Factories;

use App\Models\House;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class HouseFactory extends Factory
{
    protected $model = House::class;

    public function definition(): array
    {
        return [
            'city' => $this->faker->city(),
            'street' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'building' => $this->faker->word(),
        ];
    }
}
