<?php

namespace Tests\Unit;

use App\Models\InstrumentCalibration;
use App\Services\CalibrationRegistry;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CalibrationRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_invalidates_cache_before_creating_calibration(): void
    {
        $mockCache = Mockery::mock(CacheRepository::class);
        $registry = new CalibrationRegistry($mockCache);
        
        $payload = [
            'marine_lab_id' => 1,
            'instrument' => 'lisst-deep',
            'drift_ppm' => 4.911,
            'validated_at' => '2025-10-01T12:00:00Z',
            'payload' => [
                'operator' => 'JP-32',
                'notes' => 'Test calibration'
            ]
        ];

        $expectedKey = 'marine-lab:1:instrument:LISST-DEEP:calibration';
        $expectedCachedData = [
            'instrument' => 'LISST-DEEP',
            'drift_ppm' => 4.911,
            'validated_at' => '2025-10-01T12:00:00Z',
            'payload' => [
                'marine_lab_id' => 1,
                'instrument' => 'lisst-deep',
                'payload' => [
                    'operator' => 'JP-32',
                    'notes' => 'Test calibration'
                ]
            ]
        ];

        $mockCache->shouldReceive('forget')
            ->with($expectedKey)
            ->once();
            
        $mockCache->shouldReceive('rememberForever')
            ->with($expectedKey, Mockery::type('Closure'))
            ->once()
            ->andReturn($expectedCachedData);

        $result = $registry->store($payload);
        
        $this->assertInstanceOf(InstrumentCalibration::class, $result);
        $this->assertEquals('LISST-DEEP', $result->instrument);
        $this->assertEquals(4.911, $result->drift_ppm);
    }

    public function test_cache_key_format_is_consistent(): void
    {
        $mockCache = Mockery::mock(CacheRepository::class);
        $registry = new CalibrationRegistry($mockCache);
        
        $payload = [
            'marine_lab_id' => 123,
            'instrument' => 'some-complex-instrument-name',
            'drift_ppm' => 1.5,
            'validated_at' => '2025-10-01T12:00:00Z',
        ];

        $expectedKey = 'marine-lab:123:instrument:SOME-COMPLEX-INSTRUMENT-NAME:calibration';

        $mockCache->shouldReceive('forget')
            ->with($expectedKey)
            ->once();
            
        $mockCache->shouldReceive('rememberForever')
            ->with($expectedKey, Mockery::type('Closure'))
            ->once()
            ->andReturn([
                'instrument' => 'SOME-COMPLEX-INSTRUMENT-NAME',
                'drift_ppm' => 1.5,
                'validated_at' => '2025-10-01T12:00:00Z',
                'payload' => []
            ]);

        $registry->store($payload);
        
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}