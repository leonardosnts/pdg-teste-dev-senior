<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OpticalTelemetryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'camera_id' => $this->resource->camera_id,
            'clarity_index' => $this->resource->clarity_index,
            'battery_percent' => $this->resource->battery_percent,
            'captured_at' => optional($this->resource->captured_at)->toIso8601String(),
        ];
    }
}
