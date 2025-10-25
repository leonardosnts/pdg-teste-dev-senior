<?php

namespace Tests\Feature;

use App\Models\ExpeditionObservation;
use App\Models\ExpeditionReport;
use App\Models\MarineLab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpeditionSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_expedition_summary_returns_correct_payload()
    {
        $lab = MarineLab::factory()->create();
        
        $reports = ExpeditionReport::factory(3)->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
            'anomaly_score' => 8.5,
        ]);

        foreach ($reports as $report) {
            ExpeditionObservation::factory(2)->create([
                'expedition_report_id' => $report->id,
                'sample_count' => 100,
                'instrument' => 'sonar-deep'
            ]);
        }

        $response = $this->getJson('/api/expeditions/summary?region=Tristan%20Ridge');

        $response->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'expedition_code',
                    'region', 
                    'anomaly_score',
                    'first_instrument',
                    'observation_samples',
                    'metadata'
                ]
            ]);

        $responseData = $response->json();
        $this->assertEquals('Tristan Ridge', $responseData[0]['region']);
        $this->assertEquals(8.5, $responseData[0]['anomaly_score']);
        $this->assertEquals(200, $responseData[0]['observation_samples']);
        $this->assertEquals('sonar-deep', $responseData[0]['first_instrument']);
    }

    public function test_expedition_summary_executes_optimized_queries()
    {
        $lab = MarineLab::factory()->create();
        
        $reports = ExpeditionReport::factory(5)->create([
            'marine_lab_id' => $lab->id,
            'region' => 'Tristan Ridge',
        ]);

        foreach ($reports as $report) {
            ExpeditionObservation::factory(3)->create([
                'expedition_report_id' => $report->id,
            ]);
        }

        DB::enableQueryLog();
        
        $response = $this->getJson('/api/expeditions/summary?region=Tristan%20Ridge');
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(3, count($queries), 
            'Expected maximum 3 queries, but executed: ' . count($queries) . ' queries'
        );

        $response->assertOk();
    }
}