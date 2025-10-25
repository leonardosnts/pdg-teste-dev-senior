<?php

namespace Tests\Unit;

use App\Contracts\OceanDriftRepositoryInterface;
use App\Models\MarineLab;
use App\Services\SalinityRiskService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class SalinityRiskServiceTest extends TestCase
{
    public function test_assess_returns_correct_structure_with_nominal_status(): void
    {
        $mockRepository = $this->createMock(OceanDriftRepositoryInterface::class);
        $lab = new MarineLab(['alias' => 'test-lab']);
        $coordinates = [
            ['lat' => -37.92, 'lng' => -11.44],
            ['lat' => -38.1, 'lng' => -11.6]
        ];

        $mockEnvelope = collect([
            '2025-10' => ['surface' => 35.2, 'mid' => 34.8, 'deep' => 34.5]
        ]);

        $mockRepository
            ->expects($this->once())
            ->method('salinityEnvelope')
            ->with($lab)
            ->willReturn($mockEnvelope);

        $mockRepository
            ->expects($this->once())
            ->method('driftProjection')
            ->with($lab, $coordinates)
            ->willReturn(2.5);

        $service = new SalinityRiskService($mockRepository);

        $result = $service->assess($lab, $coordinates);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('test-lab', $result->get('lab'));
        $this->assertEquals($mockEnvelope, $result->get('envelope'));
        $this->assertEquals(2.5, $result->get('projection'));
        $this->assertEquals('nominal', $result->get('status'));
    }

    public function test_assess_returns_critical_status_when_projection_exceeds_threshold(): void
    {
        $mockRepository = $this->createMock(OceanDriftRepositoryInterface::class);
        $lab = new MarineLab(['alias' => 'test-lab']);
        $coordinates = [['lat' => -37.92, 'lng' => -11.44]];

        $mockRepository
            ->method('salinityEnvelope')
            ->willReturn(collect([]));

        $mockRepository
            ->method('driftProjection')
            ->willReturn(4.5);

        $service = new SalinityRiskService($mockRepository);

        $result = $service->assess($lab, $coordinates);

        $this->assertEquals('critical', $result->get('status'));
        $this->assertEquals(4.5, $result->get('projection'));
    }
}