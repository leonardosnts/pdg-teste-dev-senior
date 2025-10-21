<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpeditionReportDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'expedition_code' => $this->resource->expedition_code,
            'region' => $this->resource->region,
            'anomaly_score' => $this->resource->anomaly_score,
            'metadata' => $this->resource->metadata,
            'observations' => $this->resource->observations->map(fn($obs) => [
                'id' => $obs->id,
                'instrument' => $obs->instrument,
                'summary' => $obs->summary,
                'sample_count' => $obs->sample_count,
            ]),
            'created_at' => optional($this->resource->created_at)->toIso8601String(),
        ];
    }
}
