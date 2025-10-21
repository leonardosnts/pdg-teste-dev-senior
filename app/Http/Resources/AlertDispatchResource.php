<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Collection
 */
class AlertDispatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'dispatched' => $this->resource->count(),
            'channels' => $this->resource,
        ];
    }
}
