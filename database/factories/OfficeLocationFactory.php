<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeLocationFactory extends Factory
{
    protected $model = OfficeLocation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city . ' HQ',
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'radius' => 50, // 50 meters
        ];
    }
}
