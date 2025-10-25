<?php

namespace Tests\Feature;

use App\Models\MarineLab;
use App\Models\SalinitySurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalinityRiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_salinity_assess_endpoint_returns_successful_response(): void
    {
        $lab = MarineLab::factory()->create(['alias' => 'pelagic-lab']);
        
        SalinitySurvey::factory()->count(3)->create([
            'marine_lab_id' => $lab->id,
            'surface_psu' => 35.2,
            'mid_psu' => 34.8,
            'deep_psu' => 34.5,
            'surveyed_at' => now()->subDays(rand(1, 30)),
        ]);

        $payload = [
            'lab_alias' => 'pelagic-lab',
            'coordinates' => [
                ['lat' => -37.92, 'lng' => -11.44],
                ['lat' => -38.1, 'lng' => -11.6],
                ['lat' => -38.35, 'lng' => -11.88],
            ]
        ];

        $response = $this->postJson('/api/salinity/assess', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'lab',
                    'envelope',
                    'projection',
                    'status'
                ]
            ])
            ->assertJson([
                'data' => [
                    'lab' => 'pelagic-lab'
                ]
            ]);

        $this->assertIsFloat($response->json('data.projection'));
        $this->assertContains($response->json('data.status'), ['critical', 'nominal']);
    }

    public function test_salinity_assess_endpoint_handles_nonexistent_lab(): void
    {
        $payload = [
            'lab_alias' => 'non-existent-lab',
            'coordinates' => [
                ['lat' => -37.92, 'lng' => -11.44]
            ]
        ];

        $response = $this->postJson('/api/salinity/assess', $payload);

        $response->assertStatus(404);
    }
}