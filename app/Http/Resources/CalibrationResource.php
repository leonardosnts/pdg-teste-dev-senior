<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CalibrationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'instrument' => $this->resource->instrument,
            'drift_ppm' => $this->resource->drift_ppm,
            'validated_at' => optional($this->resource->validated_at)->toIso8601String(),
            'payload' => $this->resource->payload,
        ];
    }
}
