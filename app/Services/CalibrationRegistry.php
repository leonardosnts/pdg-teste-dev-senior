<?php

namespace App\Services;

use App\Models\InstrumentCalibration;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CalibrationRegistry
{
    public function __construct(private CacheRepository $cache) {}

    public function store(array $payload): InstrumentCalibration
    {
        $instrument = Str::of($payload['instrument'])->upper();
        $key = sprintf('marine-lab:%s:instrument:%s:calibration', $payload['marine_lab_id'], $instrument);

        $this->cache->forget($key);

        $cached = $this->cache->rememberForever($key, function () use ($payload, $instrument) {
            return [
                'instrument' => (string) $instrument,
                'drift_ppm' => (float) $payload['drift_ppm'],
                'validated_at' => $payload['validated_at'],
                'payload' => Arr::except($payload, ['drift_ppm', 'validated_at']),
            ];
        });

        return InstrumentCalibration::create([
            'marine_lab_id' => $payload['marine_lab_id'],
            'instrument' => $cached['instrument'],
            'drift_ppm' => $cached['drift_ppm'],
            'validated_at' => $cached['validated_at'],
            'payload' => $cached['payload'],
        ]);
    }
}
