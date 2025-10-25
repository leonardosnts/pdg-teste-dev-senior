<?php

namespace Tests\Unit;

use App\Models\ExpeditionObservation;
use App\Models\ExpeditionReport;
use App\Models\MarineLab;
use App\Services\ExpeditionReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpeditionReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExpeditionReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExpeditionReportService();
    }

    public function test_summary_returns_transformed_collection()
    {
        $lab = MarineLab::factory()->create();
        
        $report = ExpeditionReport::factory()->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
            'expedition_code' => 'EXP-001',
            'anomaly_score' => 7.5,
            'metadata' => ['depth' => 500, 'temperature' => 12.3]
        ]);

        ExpeditionObservation::factory()->create([
            'expedition_report_id' => $report->id,
            'instrument' => 'acoustic-sensor',
            'sample_count' => 150,
        ]);

        ExpeditionObservation::factory()->create([
            'expedition_report_id' => $report->id,
            'instrument' => 'optical-sensor', 
            'sample_count' => 75,
        ]);

        $result = $this->service->summary('Tristan Ridge');

        $this->assertCount(1, $result);
        
        $summary = $result->first();
        $this->assertEquals('EXP-001', $summary['expedition_code']);
        $this->assertEquals('Tristan Ridge', $summary['region']);
        $this->assertEquals(7.5, $summary['anomaly_score']);
        $this->assertEquals('acoustic-sensor', $summary['first_instrument']);
        $this->assertEquals(225, $summary['observation_samples']); // 150 + 75
        $this->assertEquals(['depth' => 500, 'temperature' => 12.3], $summary['metadata']);
    }

    public function test_summary_orders_by_anomaly_score_desc()
    {
        $lab = MarineLab::factory()->create();
        
        $report1 = ExpeditionReport::factory()->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
            'anomaly_score' => 5.0,
        ]);

        $report2 = ExpeditionReport::factory()->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge', 
            'anomaly_score' => 9.2,
        ]);

        $report3 = ExpeditionReport::factory()->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
            'anomaly_score' => 7.1,
        ]);

        $result = $this->service->summary('Tristan Ridge');

        $this->assertCount(3, $result);
        $this->assertEquals(9.2, $result->first()['anomaly_score']);
        $this->assertEquals(7.1, $result->skip(1)->first()['anomaly_score']);
        $this->assertEquals(5.0, $result->last()['anomaly_score']);
    }

    public function test_summary_limits_results_to_25()
    {
        $lab = MarineLab::factory()->create();
        
        ExpeditionReport::factory(30)->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
        ]);

        $result = $this->service->summary('Tristan Ridge');

        $this->assertCount(25, $result);
    }

    public function test_summary_filters_by_region()
    {
        $lab = MarineLab::factory()->create();
        
        ExpeditionReport::factory(3)->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
        ]);

        ExpeditionReport::factory(2)->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Atlantic Deep',
        ]);

        $result = $this->service->summary('Tristan Ridge');

        $this->assertCount(3, $result);
        $result->each(function ($item) {
            $this->assertEquals('Tristan Ridge', $item['region']);
        });
    }
}