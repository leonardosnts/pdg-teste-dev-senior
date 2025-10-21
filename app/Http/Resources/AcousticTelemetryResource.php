<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AcousticTelemetryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'beacon_id' => $this->resource->beacon_id,
            'signal_strength' => $this->resource->signal_strength,
            'battery_percent' => $this->resource->battery_percent,
            'captured_at' => optional($this->resource->captured_at)->toIso8601String(),
        ];
    }
}
