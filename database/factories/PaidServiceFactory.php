<?php

namespace Database\Factories;

use App\Models\PaidService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PaidServiceFactory extends Factory
{
    protected $model = PaidService::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
