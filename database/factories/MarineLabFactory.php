<?php

namespace Database\Factories;

use App\Models\MarineLab;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarineLabFactory extends Factory
{
    protected $model = MarineLab::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Marine Laboratory',
            'alias' => $this->faker->unique()->slug(2),
            'ocean_basin' => $this->faker->randomElement(['Atlantic', 'Pacific', 'Indian', 'Arctic', 'Southern']),
            'metadata' => [
                'location' => $this->faker->city . ', ' . $this->faker->country,
                'established_year' => $this->faker->numberBetween(1950, 2024),
                'research_focus' => $this->faker->randomElement([
                    'Microplastic pollution',
                    'Marine biodiversity',
                    'Ocean temperature monitoring',
                    'Salinity studies',
                    'Acoustic telemetry'
                ]),
                'coordinates' => [
                    'latitude' => $this->faker->latitude,
                    'longitude' => $this->faker->longitude
                ]
            ]
        ];
    }
}
