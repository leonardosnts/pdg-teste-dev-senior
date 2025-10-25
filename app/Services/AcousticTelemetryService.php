<?php

namespace App\Services;

use App\Models\AcousticTelemetryReading;
use App\Models\MarineLab;
use App\Traits\TelemetryNormalizationTrait;
use Illuminate\Support\Arr;

class AcousticTelemetryService
{
    use TelemetryNormalizationTrait;
    public function ingest(MarineLab $lab, array $payload): AcousticTelemetryReading
    {
        $normalized = $this->normalizePayload($payload);

        return AcousticTelemetryReading::create([
            'marine_lab_id' => $lab->id,
            'beacon_id' => $normalized['beacon_id'],
            'signal_strength' => $normalized['signal_strength'],
            'battery_percent' => $normalized['battery_percent'],
            'captured_at' => $normalized['captured_at'],
        ]);
    }

    private function normalizePayload(array $payload): array
    {
        $common = $this->normalizeCommonFields($payload, [
            'battery_math' => 'ceil',
            'captured_default' => now()
        ]);
        
        return array_merge($common, [
            'beacon_id' => (string) Arr::get($payload, 'beacon_id', Arr::get($payload, 'beacon')),
            'signal_strength' => round((float) Arr::get($payload, 'signal_strength', -121.7), 2),
        ]);
    }
}
