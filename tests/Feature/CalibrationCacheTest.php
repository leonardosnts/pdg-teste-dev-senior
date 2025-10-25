<?php

namespace Tests\Feature;

use App\Models\InstrumentCalibration;
use App\Models\MarineLab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CalibrationCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_calibration_cache_is_invalidated_on_new_calibration(): void
    {
        $marineLab = MarineLab::factory()->create();
        
        $firstCalibrationData = [
            'marine_lab_id' => $marineLab->id,
            'instrument' => 'lisst-deep',
            'drift_ppm' => 4.911,
            'validated_at' => '2025-10-01T12:00:00Z',
            'payload' => [
                'operator' => 'JP-32',
                'notes' => 'Calibração inicial'
            ]
        ];

        $secondCalibrationData = [
            'marine_lab_id' => $marineLab->id,
            'instrument' => 'lisst-deep',
            'drift_ppm' => 7.245,
            'validated_at' => '2025-10-02T14:00:00Z',
            'payload' => [
                'operator' => 'JP-32',
                'notes' => 'Recalibração após tempestade'
            ]
        ];

        $response1 = $this->postJson('/api/calibrations', $firstCalibrationData);
        $response1->assertStatus(200);
        
        $firstCalibration = InstrumentCalibration::latest()->first();
        $this->assertEquals(4.911, $firstCalibration->drift_ppm);
        $this->assertEquals('Calibração inicial', $firstCalibration->payload['notes']);

        $response2 = $this->postJson('/api/calibrations', $secondCalibrationData);
        $response2->assertStatus(200);
        
        $secondCalibration = InstrumentCalibration::latest()->first();
        $this->assertEquals(7.245, $secondCalibration->drift_ppm);
        $this->assertEquals('Recalibração após tempestade', $secondCalibration->payload['notes']);
        
        $this->assertDatabaseCount('instrument_calibrations', 2);
    }

    public function test_cache_key_is_properly_invalidated(): void
    {
        Cache::spy();
        $marineLab = MarineLab::factory()->create();
        
        $calibrationData = [
            'marine_lab_id' => $marineLab->id,
            'instrument' => 'lisst-deep',
            'drift_ppm' => 4.911,
            'validated_at' => '2025-10-01T12:00:00Z',
            'payload' => [
                'operator' => 'JP-32',
                'notes' => 'Teste de cache'
            ]
        ];

        $expectedCacheKey = sprintf('marine-lab:%s:instrument:LISST-DEEP:calibration', $marineLab->id);

        $this->postJson('/api/calibrations', $calibrationData);

        Cache::shouldHaveReceived('forget')->with($expectedCacheKey)->once();
        Cache::shouldHaveReceived('rememberForever')->with($expectedCacheKey, \Mockery::type('Closure'))->once();
    }
}