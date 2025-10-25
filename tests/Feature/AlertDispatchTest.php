<?php

namespace Tests\Feature;

use App\Models\AlertChannel;
use App\Models\MarineLab;
use App\Services\AlertDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function it_dispatches_alerts_to_default_channels()
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'email',
            'endpoint' => 'alerts@test.com',
        ]);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'sms',
            'endpoint' => '+5511999999999',
        ]);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'satellite',
            'endpoint' => 'SAT-001',
        ]);

        $response = $this->postJson('/api/alerts/dispatch', [
            'lab_alias' => 'test-lab',
            'event_type' => 'SATELLITE_DRIFT',
            'payload' => [
                'summary' => 'Deriva crítica detectada',
                'details' => [
                    'sector' => 'Tristan Ridge',
                    'threshold' => 3.6,
                ],
            ],
            'triggered_at' => '2025-10-20T17:45:00Z',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(3, $data);
        
        $adapters = collect($data)->pluck('adapter')->toArray();
        $this->assertContains('email', $adapters);
        $this->assertContains('sms', $adapters);
        $this->assertContains('satellite', $adapters);
    }

    public function it_allows_registering_custom_adapters()
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'slack',
            'endpoint' => 'https://hooks.slack.com/test',
        ]);

        $alertService = app(AlertDispatchService::class);
        $alertService->registerAdapter('slack', function($channel, $event) {
            return [
                'channel' => 'slack',
                'status' => 'posted',
                'transport' => 'webhook',
                'payload' => [
                    'webhook_url' => $channel->endpoint,
                    'text' => sprintf('[%s] %s', $event->event_type, data_get($event->payload, 'summary')),
                ],
            ];
        });

        $response = $this->postJson('/api/alerts/dispatch', [
            'lab_alias' => 'test-lab',
            'event_type' => 'CRITICAL_ALERT',
            'payload' => ['summary' => 'Test alert'],
            'triggered_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('slack', $data[0]['adapter']);
        $this->assertEquals('slack', $data[0]['channel']);
        $this->assertEquals('posted', $data[0]['status']);
    }

    public function it_uses_fallback_for_unknown_channels()
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'unknown_channel',
            'endpoint' => 'some-endpoint',
        ]);

        $response = $this->postJson('/api/alerts/dispatch', [
            'lab_alias' => 'test-lab',
            'event_type' => 'TEST_ALERT',
            'payload' => ['summary' => 'Test'],
            'triggered_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals('unknown_channel', $data[0]['adapter']);
        $this->assertEquals('unknown_channel', $data[0]['channel']);
        $this->assertEquals('discarded', $data[0]['status']);
    }

    public function it_identifies_adapter_in_response()
    {
        $lab = MarineLab::factory()->create(['alias' => 'test-lab']);
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'email',
            'endpoint' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/alerts/dispatch', [
            'lab_alias' => 'test-lab',
            'event_type' => 'TEST',
            'payload' => ['summary' => 'Test'],
            'triggered_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertArrayHasKey('adapter', $data[0]);
        $this->assertEquals('email', $data[0]['adapter']);
    }
}
