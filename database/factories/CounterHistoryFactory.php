<?php

namespace Database\Factories;

use App\Models\CounterData;
use App\Models\CounterHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CounterHistoryFactory extends Factory
{
    protected $model = CounterHistory::class;

    public function definition(): array
    {
        return [
            'counter_name_id' => CounterData::factory(),
            'approved' => $this->faker->boolean(),
            'from_1c' => $this->faker->boolean(),
            'daily_consumption' => $this->faker->randomFloat(2, 0, 1000),
            'night_consumption' => $this->faker->randomFloat(2, 0, 1000),
            'peak_consumption' => $this->faker->randomFloat(2, 0, 1000),
            'last_checked_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
