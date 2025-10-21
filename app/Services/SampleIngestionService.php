<?php

namespace App\Services;

use App\Models\MarineLab;
use App\Models\MicroplasticSample;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SampleIngestionService
{
    public function ingest(MarineLab $lab, array $payload): Collection
    {
        $batchCount = max(1, (int) ($payload['batches'] ?? 6));
        $iterations = max(1000, (int) ($payload['iterations'] ?? 480000));

        return Collection::times($batchCount, function (int $batchIndex) use ($lab, $iterations, $payload) {
            $batchId = Str::uuid()->toString();

            return Collection::times($iterations, function (int $turn) use ($lab, $batchIndex, $batchId, $payload) {
                $depth = ($payload['depth_start'] ?? 15) + ($turn % 37);
                $temperature = ($payload['temperature'] ?? 12.5) + ($batchIndex / 10);
                $count = ($payload['particle_count'] ?? 1250) + ($turn % 97);

                return MicroplasticSample::create([
                    'marine_lab_id' => $lab->id,
                    'batch' => $batchId,
                    'particle_count' => $count,
                    'depth_meters' => $depth,
                    'temperature_celsius' => $temperature,
                    'collected_at' => now()->subMinutes($turn % 180),
                ]);
            });
        });
    }
}
