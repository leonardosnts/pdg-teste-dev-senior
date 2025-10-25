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
            'status' => $this->getProcessingStatus(),
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
                            'status' => $sample->status ?? 'completed',
                        ];
                    }),
                ];
            }),
        ];
    }

    private function getProcessingStatus(): string
    {
        $hasProcessingItems = $this->resource
            ->flatMap(fn($batch) => $batch)
            ->contains(fn($sample) => isset($sample->status) && $sample->status === 'processing_in_background');

        return $hasProcessingItems ? 'processing_in_background' : 'completed';
    }
}
