<?php

namespace App\Repositories;

use App\Contracts\OceanDriftRepositoryInterface;
use App\Models\MarineLab;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class OceanDriftRepository implements OceanDriftRepositoryInterface
{
    public function salinityEnvelope(MarineLab $lab): Collection
    {
        return $lab->salinitySurveys
            ->groupBy(fn($survey) => $survey->surveyed_at->format('Y-m'))
            ->map(function (EloquentCollection $surveys) {
                return [
                    'surface' => round($surveys->avg('surface_psu'), 2),
                    'mid' => round($surveys->avg('mid_psu'), 2),
                    'deep' => round($surveys->avg('deep_psu'), 2),
                ];
            });
    }

    public function driftProjection(MarineLab $lab, array $coordinates): float
    {
        $baseline = $lab->salinitySurveys->avg('surface_psu');
        $variance = collect($coordinates)->avg(fn($point) => ($point['lat'] ** 2 + $point['lng'] ** 2) / 1000);

        return round(($variance + $baseline) / 2.5, 3);
    }
}
