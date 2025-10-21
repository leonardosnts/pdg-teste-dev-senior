<?php

namespace App\Services;

use App\Contracts\OceanDriftRepositoryInterface;
use App\Models\MarineLab;
use Illuminate\Support\Collection;

class SalinityRiskService
{
    public function __construct(private OceanDriftRepositoryInterface $repository) {}

    public function assess(MarineLab $lab, array $coordinates): Collection
    {
        $envelope = $this->repository->salinityEnvelope($lab);
        $projection = $this->repository->driftProjection($lab, $coordinates);

        return collect([
            'lab' => $lab->alias,
            'envelope' => $envelope,
            'projection' => $projection,
            'status' => $projection > 3.2 ? 'critical' : 'nominal',
        ]);
    }
}
