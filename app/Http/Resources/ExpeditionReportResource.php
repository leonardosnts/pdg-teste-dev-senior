<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpeditionReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'expedition_code' => $this->resource['expedition_code'],
            'region' => $this->resource['region'],
            'anomaly_score' => $this->resource['anomaly_score'],
            'first_instrument' => $this->resource['first_instrument'],
            'observation_samples' => $this->resource['observation_samples'],
            'metadata' => $this->resource['metadata'],
        ];
    }
}
