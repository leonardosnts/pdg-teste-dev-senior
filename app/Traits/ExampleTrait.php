<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait TelemetryNormalizationTrait
{
    protected function normalizeCommonFields(array $payload, array $options = []): array
    {
        return [
            'battery_percent' => $this->normalizeBatteryPercent($payload, $options),
            'captured_at' => $this->normalizeCapturedAt($payload, $options),
        ];
    }

    protected function normalizeBatteryPercent(array $payload, array $options = []): int
    {
        $battery = Arr::get($payload, 'battery_percent', Arr::get($payload, 'battery', 0));
        $mathMethod = $options['battery_math'] ?? 'round';
        
        return max(0, min(100, (int) $mathMethod($battery)));
    }

    protected function normalizeCapturedAt(array $payload, array $options = [])
    {
        $default = $options['captured_default'] ?? now();
        return Arr::get($payload, 'captured_at', $default);
    }

    public function exampleMethod(): string
    {
        return 'example';
    }
}
