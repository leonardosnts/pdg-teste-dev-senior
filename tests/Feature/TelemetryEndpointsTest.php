<?php

namespace Tests\Feature;

use App\Models\MarineLab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        MarineLab::factory()->create([
            'alias' => 'pelagic-lab',
            'name' => 'Pelagic Research Lab'
        ]);
    }

    public function test_acoustic_telemetry_ingestion()
    {
        $payload = [
            'lab_alias' => 'pelagic-lab',
            'beacon_id' => 'AC-5541',
            'signal_strength' => -119.27,
            'battery_percent' => 47.3,
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/acoustic', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'beacon_id',
                     'signal_strength', 
                     'battery_percent',
                     'captured_at'
                 ]);

        $this->assertEquals('AC-5541', $response->json('beacon_id'));
        $this->assertEquals(-119.27, $response->json('signal_strength'));
        $this->assertEquals(48, $response->json('battery_percent'));
        $this->assertEquals('2025-10-20T18:31:00Z', $response->json('captured_at'));
    }

    public function test_optical_telemetry_ingestion()
    {
        $payload = [
            'lab_alias' => 'pelagic-lab',
            'camera_id' => 'OP-8821',
            'clarity_index' => 2.71828,
            'battery_percent' => 47.3,
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/optical', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'camera_id',
                     'clarity_index',
                     'battery_percent', 
                     'captured_at'
                 ]);

        $this->assertEquals('OP-8821', $response->json('camera_id'));
        $this->assertEquals(2.718, $response->json('clarity_index'));
        $this->assertEquals(47, $response->json('battery_percent'));
        $this->assertEquals('2025-10-20T18:31:00Z', $response->json('captured_at'));
    }

    public function test_acoustic_telemetry_with_missing_battery_uses_default()
    {
        $payload = [
            'lab_alias' => 'pelagic-lab',
            'beacon_id' => 'AC-5541',
            'signal_strength' => -119.27,
            // battery_percent ausente
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/acoustic', $payload);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('battery_percent'));
    }

    public function test_optical_telemetry_with_missing_battery_uses_default()
    {
        $payload = [
            'lab_alias' => 'pelagic-lab',  
            'camera_id' => 'OP-8821',
            'clarity_index' => 2.71828,
            // battery_percent ausente
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/optical', $payload);

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('battery_percent'));
    }

    public function test_battery_percent_boundary_conditions_acoustic()
    {
        // Teste bateria negativa
        $payload = [
            'lab_alias' => 'pelagic-lab',
            'beacon_id' => 'AC-5541', 
            'signal_strength' => -119.27,
            'battery_percent' => -10,
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/acoustic', $payload);
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('battery_percent'));

        // Teste bateria acima de 100
        $payload['battery_percent'] = 150;
        $response = $this->postJson('/api/telemetry/acoustic', $payload);
        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('battery_percent'));
    }

    public function test_battery_percent_boundary_conditions_optical()
    {
        $payload = [
            'lab_alias' => 'pelagic-lab',
            'camera_id' => 'OP-8821',
            'clarity_index' => 2.71828,
            'battery_percent' => -10,
            'captured_at' => '2025-10-20T18:31:00Z'
        ];

        $response = $this->postJson('/api/telemetry/optical', $payload);
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('battery_percent'));

        $payload['battery_percent'] = 150;
        $response = $this->postJson('/api/telemetry/optical', $payload);
        $response->assertStatus(200);
        $this->assertEquals(100, $response->json('battery_percent'));
    }
}