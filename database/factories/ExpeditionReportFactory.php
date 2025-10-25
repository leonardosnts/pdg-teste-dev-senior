<?php

namespace Database\Factories;

use App\Models\ExpeditionReport;
use App\Models\MarineLab;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpeditionReportFactory extends Factory
{
    protected $model = ExpeditionReport::class;

    public function definition(): array
    {
        return [
            'marine_lab_id' => MarineLab::factory(),
            'expedition_code' => 'EXP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'region' => $this->faker->randomElement([
                'Tristan Ridge',
                'Atlantic Deep', 
                'Pacific Basin',
                'Southern Ocean',
                'Arctic Zone'
            ]),
            'anomaly_score' => $this->faker->randomFloat(1, 0, 10),
            'metadata' => [
                'depth' => $this->faker->numberBetween(10, 5000),
                'temperature' => $this->faker->randomFloat(1, -2, 30),
                'salinity' => $this->faker->randomFloat(2, 30, 40),
            ],
        ];
    }
}