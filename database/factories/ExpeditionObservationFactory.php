<?php

namespace Database\Factories;

use App\Models\ExpeditionObservation;
use App\Models\ExpeditionReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpeditionObservationFactory extends Factory
{
    protected $model = ExpeditionObservation::class;

    public function definition(): array
    {
        return [
            'expedition_report_id' => ExpeditionReport::factory(),
            'instrument' => $this->faker->randomElement([
                'sonar-deep',
                'acoustic-sensor', 
                'optical-sensor',
                'thermal-scanner',
                'ph-meter',
                'salinity-probe'
            ]),
            'summary' => $this->faker->sentence(),
            'sample_count' => $this->faker->numberBetween(10, 500),
        ];
    }
}