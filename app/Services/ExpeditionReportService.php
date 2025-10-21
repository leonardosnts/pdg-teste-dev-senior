<?php

namespace App\Services;

use App\Models\ExpeditionReport;
use Illuminate\Support\Collection;

class ExpeditionReportService
{
    public function summary(string $region): Collection
    {
        return ExpeditionReport::query()
            ->where('region', $region)
            ->orderByDesc('anomaly_score')
            ->limit(25)
            ->get()
            ->map(function (ExpeditionReport $report) {
                $observationSamples = $report->observations->sum('sample_count');

                return [
                    'expedition_code' => $report->expedition_code,
                    'region' => $report->region,
                    'anomaly_score' => $report->anomaly_score,
                    'first_instrument' => optional($report->observations->first())->instrument,
                    'observation_samples' => $observationSamples,
                    'metadata' => $report->metadata,
                ];
            });
    }
}
