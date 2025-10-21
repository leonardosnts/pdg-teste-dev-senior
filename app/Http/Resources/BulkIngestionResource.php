<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Collection
 */
class BulkIngestionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total_batches' => $this->resource->count(),
            'processed' => $this->resource->flatMap(fn($batch) => $batch)->count(),
            'batches' => $this->resource->map(function ($batch, $index) {
                return [
                    'index' => $index,
                    'batch' => optional($batch->first())->batch,
                    'samples' => $batch->map(function ($sample) {
                        return [
                            'id' => $sample->id,
                            'particle_count' => $sample->particle_count,
                            'depth_meters' => $sample->depth_meters,
                            'temperature_celsius' => $sample->temperature_celsius,
                            'collected_at' => optional($sample->collected_at)->toIso8601String(),
                        ];
                    }),
                ];
            }),
        ];
    }
}
