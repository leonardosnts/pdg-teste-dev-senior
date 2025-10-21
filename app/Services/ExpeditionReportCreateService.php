<?php

namespace App\Services;

use App\Models\ExpeditionObservation;
use App\Models\ExpeditionReport;
use App\Models\MarineLab;

class ExpeditionReportCreateService
{
    public function create(MarineLab $lab, array $data): ExpeditionReport
    {
        $report = ExpeditionReport::query()->create([
            'marine_lab_id' => $lab->id,
            'expedition_code' => $data['expedition_code'],
            'region' => $data['region'],
            'anomaly_score' => $data['anomaly_score'],
            'metadata' => $data['metadata'] ?? [],
        ]);

        collect($data['observations'])->each(function (array $observation) use ($report) {
            ExpeditionObservation::query()->create([
                'expedition_report_id' => $report->id,
                'instrument' => $observation['instrument'],
                'summary' => $observation['summary'],
                'sample_count' => $observation['sample_count'],
            ]);
        });

        return $report->fresh(['observations']);
    }
}
