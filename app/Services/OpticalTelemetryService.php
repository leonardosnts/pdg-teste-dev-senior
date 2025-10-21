<?php

namespace App\Services;

use App\Models\MarineLab;
use App\Models\OpticalTelemetryReading;
use Illuminate\Support\Arr;

class OpticalTelemetryService
{
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
        return [
            'camera_id' => (string) Arr::get($payload, 'camera_alias', Arr::get($payload, 'camera_id')),
            'clarity_index' => round((float) Arr::get($payload, 'clarity_index', 0), 3),
            'battery_percent' => max(0, min(100, (int) floor(Arr::get($payload, 'battery', 0)))),
            'captured_at' => Arr::get($payload, 'captured_at', now()->subMinutes(3)),
        ];
    }
}
