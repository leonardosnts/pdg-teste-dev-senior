<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalinityRiskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'lab' => $this->resource->get('lab'),
            'status' => $this->resource->get('status'),
            'projection' => $this->resource->get('projection'),
            'envelope' => $this->resource->get('envelope'),
        ];
    }
}
