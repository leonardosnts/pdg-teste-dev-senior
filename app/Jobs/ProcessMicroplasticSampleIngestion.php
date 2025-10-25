<?php

namespace App\Jobs;

use App\Models\MarineLab;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessMicroplasticSampleIngestion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;
    public int $maxExceptions = 3;

    public function __construct(
        public int $marineLabId,
        public array $payload
    ) {}

    public function handle(): void
    {
        $startTime = microtime(true);

        Log::info('Starting microplastic sample ingestion', [
            'lab_id' => $this->marineLabId,
            'payload' => $this->payload
        ]);

        $lab = MarineLab::find($this->marineLabId);
        if (!$lab) {
            Log::error('Marine lab not found', ['lab_id' => $this->marineLabId]);
            $this->fail('Marine lab not found');
            return;
        }

        $batchCount = max(1, (int) ($this->payload['batches'] ?? 6));
        $iterations = max(1000, (int) ($this->payload['iterations'] ?? 480000));
        $totalSamples = $batchCount * $iterations;

        Log::info('Processing samples', [
            'lab' => $lab->alias,
            'batches' => $batchCount,
            'iterations' => $iterations,
            'total_samples' => $totalSamples
        ]);

        $samples = [];
        $now = now();
        $insertedCount = 0;

        for ($batchIndex = 1; $batchIndex <= $batchCount; $batchIndex++) {
            $batchId = Str::uuid()->toString();

            for ($turn = 1; $turn <= $iterations; $turn++) {
                $depth = ($this->payload['depth_start'] ?? 15) + ($turn % 37);
                $temperature = ($this->payload['temperature'] ?? 12.5) + ($batchIndex / 10);
                $count = ($this->payload['particle_count'] ?? 1250) + ($turn % 97);

                $samples[] = [
                    'marine_lab_id' => $lab->id,
                    'batch' => $batchId,
                    'particle_count' => $count,
                    'depth_meters' => $depth,
                    'temperature_celsius' => $temperature,
                    'collected_at' => $now->clone()->subMinutes($turn % 180),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($samples) >= 1000) {
                    $this->insertSamplesChunk($samples);
                    $insertedCount += count($samples);
                    $samples = [];

                    if ($insertedCount % 10000 === 0) {
                        Log::info('Progress update', [
                            'lab' => $lab->alias,
                            'inserted' => $insertedCount,
                            'total' => $totalSamples,
                            'progress' => round(($insertedCount / $totalSamples) * 100, 2) . '%'
                        ]);
                    }
                }
            }
        }

        if (!empty($samples)) {
            $this->insertSamplesChunk($samples);
            $insertedCount += count($samples);
        }

        $duration = round(microtime(true) - $startTime, 2);

        Log::info('Microplastic sample ingestion completed', [
            'lab' => $lab->alias,
            'total_inserted' => $insertedCount,
            'duration_seconds' => $duration,
            'samples_per_second' => round($insertedCount / $duration)
        ]);
    }

    private function insertSamplesChunk(array $samples): void
    {
        try {
            DB::table('microplastic_samples')->insert($samples);
        } catch (\Exception $e) {
            Log::error('Failed to insert samples chunk', [
                'error' => $e->getMessage(),
                'chunk_size' => count($samples)
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Microplastic sample ingestion job failed', [
            'lab_id' => $this->marineLabId,
            'payload' => $this->payload,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
