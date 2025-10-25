<?php

namespace Tests\Feature;

use App\Jobs\ProcessMicroplasticSampleIngestion;
use App\Models\MarineLab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BulkSampleIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_returns_immediate_response_with_tracking_info(): void
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);

        $payload = [
            'lab_alias' => 'test-lab',
            'batches' => 4,
            'iterations' => 200000,
            'particle_count' => 1400,
        ];

        $response = $this->postJson('/api/marine-labs/ingest-samples', $payload);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'job_id',
                'status',
                'lab_alias',
                'message',
                'estimated_samples',
                'started_at'
            ]);

        $this->assertEquals('processing', $response->json('status'));
        $this->assertEquals('test-lab', $response->json('lab_alias'));
        $this->assertEquals(800000, $response->json('estimated_samples')); // 4 * 200000
        $this->assertNotNull($response->json('job_id'));
    }

    public function test_dispatches_job_to_redis_queue(): void
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);

        $payload = [
            'lab_alias' => 'test-lab',
            'batches' => 2,
            'iterations' => 10000,
        ];

        $this->postJson('/api/marine-labs/ingest-samples', $payload);

        Queue::assertPushed(ProcessMicroplasticSampleIngestion::class, function ($job) {
            return $job->queue === 'sample-ingestion';
        });
    }

    public function test_all_requests_are_asynchronous(): void
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);

        $smallPayload = [
            'lab_alias' => 'test-lab',
            'batches' => 1,
            'iterations' => 1000,
        ];

        $largePayload = [
            'lab_alias' => 'test-lab',
            'batches' => 4,
            'iterations' => 200000,
        ];

        $smallResponse = $this->postJson('/api/marine-labs/ingest-samples', $smallPayload);
        $largeResponse = $this->postJson('/api/marine-labs/ingest-samples', $largePayload);

        $smallResponse->assertStatus(202);
        $largeResponse->assertStatus(202);

        Queue::assertPushed(ProcessMicroplasticSampleIngestion::class, 2);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/marine-labs/ingest-samples', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lab_alias']);
    }

    public function test_validates_lab_alias_exists(): void
    {
        $response = $this->postJson('/api/marine-labs/ingest-samples', [
            'lab_alias' => 'nonexistent-lab'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lab_alias']);
    }

    public function test_response_time_is_fast(): void
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);

        $payload = [
            'lab_alias' => 'test-lab',
            'batches' => 4,
            'iterations' => 200000,
        ];

        $startTime = microtime(true);
        $response = $this->postJson('/api/marine-labs/ingest-samples', $payload);
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;
        $response->assertStatus(202);

        $this->assertLessThan(1.0, $responseTime, 'Response time should be under 1 second');
    }
}
