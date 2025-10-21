<?php

namespace App\Services;

use App\Models\AcousticTelemetryReading;
use App\Models\MarineLab;
use Illuminate\Support\Arr;

class AcousticTelemetryService
{
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
        return [
            'beacon_id' => (string) Arr::get($payload, 'beacon_id', Arr::get($payload, 'beacon')),
            'signal_strength' => round((float) Arr::get($payload, 'signal_strength', -121.7), 2),
            'battery_percent' => max(0, min(100, (int) ceil(Arr::get($payload, 'battery_percent', 0)))),
            'captured_at' => Arr::get($payload, 'captured_at', now()),
        ];
    }
}
