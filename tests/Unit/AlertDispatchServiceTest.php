<?php

namespace Tests\Unit;

use App\Models\AlertChannel;
use App\Models\AlertEvent;
use App\Models\MarineLab;
use App\Services\AlertDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertDispatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function it_can_register_custom_adapters()
    {
        $service = new AlertDispatchService();
        
        $service->registerAdapter('custom', function($channel, $event) {
            return [
                'channel' => 'custom',
                'status' => 'sent',
                'custom_field' => 'custom_value',
            ];
        });
        
        $lab = MarineLab::factory()->create();
        $channel = AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'custom',
            'endpoint' => 'test-endpoint',
        ]);
        
        $event = AlertEvent::create([
            'marine_lab_id' => $lab->id,
            'event_type' => 'TEST',
            'payload' => ['summary' => 'Test'],
            'triggered_at' => now(),
        ]);
        
        $results = $service->dispatch($lab, $event);
        $result = $results->first();
        
        $this->assertEquals('custom', $result['adapter']);
        $this->assertEquals('custom', $result['channel']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('custom_value', $result['custom_field']);
    }

    public function it_uses_fallback_for_unregistered_channels()
    {
        $service = new AlertDispatchService();
        
        $lab = MarineLab::factory()->create();
        $channel = AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'unregistered',
            'endpoint' => 'test-endpoint',
        ]);
        
        $event = AlertEvent::create([
            'marine_lab_id' => $lab->id,
            'event_type' => 'TEST',
            'payload' => ['summary' => 'Test'],
            'triggered_at' => now(),
        ]);
        
        $results = $service->dispatch($lab, $event);
        $result = $results->first();
        
        $this->assertEquals('unregistered', $result['adapter']);
        $this->assertEquals('unregistered', $result['channel']);
        $this->assertEquals('discarded', $result['status']);
    }

    public function it_processes_multiple_channels()
    {
        $service = new AlertDispatchService();
        
        $lab = MarineLab::factory()->create();
        
        AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'email',
            'endpoint' => 'test@example.com',
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
        
        $event = AlertEvent::create([
            'marine_lab_id' => $lab->id,
            'event_type' => 'CRITICAL',
            'payload' => ['summary' => 'Critical alert'],
            'triggered_at' => now(),
        ]);
        
        $results = $service->dispatch($lab, $event);
        
        $this->assertCount(3, $results);
        
        $adapters = $results->pluck('adapter')->toArray();
        $this->assertContains('email', $adapters);
        $this->assertContains('sms', $adapters);
        $this->assertContains('satellite', $adapters);
    }

    public function it_identifies_adapter_in_each_response()
    {
        $service = new AlertDispatchService();
        
        $lab = MarineLab::factory()->create();
        $channel = AlertChannel::create([
            'marine_lab_id' => $lab->id,
            'channel' => 'email',
            'endpoint' => 'test@example.com',
        ]);
        
        $event = AlertEvent::create([
            'marine_lab_id' => $lab->id,
            'event_type' => 'TEST',
            'payload' => ['summary' => 'Test'],
            'triggered_at' => now(),
        ]);
        
        $results = $service->dispatch($lab, $event);
        $result = $results->first();
        
        $this->assertArrayHasKey('adapter', $result);
        $this->assertEquals('email', $result['adapter']);
    }
}
