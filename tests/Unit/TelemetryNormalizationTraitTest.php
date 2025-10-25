<?php

namespace Tests\Unit;

use App\Traits\TelemetryNormalizationTrait;
use Tests\TestCase;

class TelemetryNormalizationTraitTest extends TestCase
{
    use TelemetryNormalizationTrait;

    public function test_normalizes_common_fields_with_defaults()
    {
        $payload = [];
        $result = $this->normalizeCommonFields($payload);
        
        $this->assertArrayHasKey('battery_percent', $result);
        $this->assertArrayHasKey('captured_at', $result);
        $this->assertEquals(0, $result['battery_percent']);
        $this->assertNotNull($result['captured_at']);
    }

    public function test_normalizes_battery_percent_with_different_math_functions()
    {
        $payload = ['battery_percent' => 47.3];
        
        $resultCeil = $this->normalizeCommonFields($payload, ['battery_math' => 'ceil']);
        $this->assertEquals(48, $resultCeil['battery_percent']);
        
        $resultFloor = $this->normalizeCommonFields($payload, ['battery_math' => 'floor']);
        $this->assertEquals(47, $resultFloor['battery_percent']);
    }

    public function test_normalizes_battery_percent_respects_boundaries()
    {
        $payload = ['battery_percent' => -10];
        $result = $this->normalizeCommonFields($payload);
        $this->assertEquals(0, $result['battery_percent']);
        
        $payload = ['battery_percent' => 150];
        $result = $this->normalizeCommonFields($payload);
        $this->assertEquals(100, $result['battery_percent']);
    }

    public function test_normalizes_captured_at_with_custom_default()
    {
        $payload = [];
        $customDefault = now()->subMinutes(3);
        
        $result = $this->normalizeCommonFields($payload, [
            'captured_default' => $customDefault
        ]);
        
        $this->assertEquals($customDefault, $result['captured_at']);
    }

    public function test_normalizes_captured_at_uses_payload_value()
    {
        $customTime = '2025-10-20T18:31:00Z';
        $payload = ['captured_at' => $customTime];
        
        $result = $this->normalizeCommonFields($payload);
        
        $this->assertEquals($customTime, $result['captured_at']);
    }

    public function test_handles_alternative_battery_field_names()
    {
        $payload = ['battery' => 50];
        $result = $this->normalizeCommonFields($payload);
        
        $this->assertEquals(50, $result['battery_percent']);
    }
}