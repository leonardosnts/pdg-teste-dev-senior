<?php

namespace Tests\Unit;

use App\Contracts\OceanDriftRepositoryInterface;
use App\Repositories\OceanDriftRepository;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\App;

class ServiceProviderBindingTest extends TestCase
{
    public function test_ocean_drift_repository_interface_is_bound_correctly(): void
    {
        $repository = App::make(OceanDriftRepositoryInterface::class);

        $this->assertInstanceOf(OceanDriftRepository::class, $repository);
        $this->assertInstanceOf(OceanDriftRepositoryInterface::class, $repository);
    }

    public function test_container_can_resolve_salinity_risk_service_with_dependency(): void
    {
        $service = App::make(\App\Services\SalinityRiskService::class);

        $this->assertInstanceOf(\App\Services\SalinityRiskService::class, $service);
    }
}