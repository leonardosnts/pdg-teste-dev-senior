<?php

namespace App\Services;

use App\Jobs\ProcessMicroplasticSampleIngestion;
use App\Models\MarineLab;
use Illuminate\Support\Str;

class SampleIngestionService
{
    public function ingest(MarineLab $lab, array $payload): array
    {
        $jobId = Str::uuid()->toString();

        ProcessMicroplasticSampleIngestion::dispatch($lab->id, $payload)
            ->onQueue('sample-ingestion')
            ->onConnection('redis');

        return [
            'job_id' => $jobId,
            'status' => 'processing',
            'lab_alias' => $lab->alias,
            'message' => 'Sample ingestion started in background',
            'estimated_samples' => $this->calculateTotalSamples($payload),
            'started_at' => now()->toISOString(),
        ];
    }

    private function calculateTotalSamples(array $payload): int
    {
        $batchCount = max(1, (int) ($payload['batches'] ?? 6));
        $iterations = max(1000, (int) ($payload['iterations'] ?? 480000));

        return $batchCount * $iterations;
    }
}
