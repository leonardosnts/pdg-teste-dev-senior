<?php

namespace App\Services;

use App\Models\MarineLab;
use App\Models\OpticalTelemetryReading;
use App\Traits\TelemetryNormalizationTrait;
use Illuminate\Support\Arr;

class OpticalTelemetryService
{
    use TelemetryNormalizationTrait;
    public function ingest(MarineLab $lab, array $payload): OpticalTelemetryReading
    {
        $normalized = $this->normalizePayload($payload);

        return OpticalTelemetryReading::create([
            'marine_lab_id' => $lab->id,
            'camera_id' => $normalized['camera_id'],
            'clarity_index' => $normalized['clarity_index'],
            'battery_percent' => $normalized['battery_percent'],
            'captured_at' => $normalized['captured_at'],
        ]);
    }

    private function normalizePayload(array $payload): array
    {
        $common = $this->normalizeCommonFields($payload, [
            'battery_math' => 'floor',
            'captured_default' => now()->subMinutes(3)
        ]);
        
        return array_merge($common, [
            'camera_id' => (string) Arr::get($payload, 'camera_alias', Arr::get($payload, 'camera_id')),
            'clarity_index' => round((float) Arr::get($payload, 'clarity_index', 0), 3),
        ]);
    }
}
