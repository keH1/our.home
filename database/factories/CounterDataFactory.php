<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\CounterData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CounterDataFactory extends Factory
{
    protected $model = CounterData::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numerify('CNT#####'),
            'verification_to' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'counter_type' => $this->faker->randomElement(['Электричество', 'Вода', 'Газ']),
            'counter_seal' => $this->faker->lexify('SEAL?????'),
            'factory_number' => $this->faker->numerify('FN#####'),
            'shutdown_reason' => $this->faker->sentence(),
            'calibration_interval' => $this->faker->numberBetween(1, 5),
            'commissioning_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'first_calibration_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'apartment_id' => Apartment::factory(),
            // Поле 'personal_number' будет установлено через связь 'for' в тестах
        ];
    }
}
