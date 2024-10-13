<?php

namespace Database\Factories;

use App\Models\PaidServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PaidServiceCategoryFactory extends Factory
{
    protected $model = PaidServiceCategory::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
