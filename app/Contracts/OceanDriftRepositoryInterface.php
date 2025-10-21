<?php

namespace App\Contracts;

use App\Models\MarineLab;
use Illuminate\Support\Collection;

interface OceanDriftRepositoryInterface
{
    public function salinityEnvelope(MarineLab $lab): Collection;

    public function driftProjection(MarineLab $lab, array $coordinates): float;
}
