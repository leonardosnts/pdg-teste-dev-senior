<?php

namespace App\Services;

use App\Models\AlertChannel;
use App\Models\AlertEvent;
use App\Models\MarineLab;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AlertDispatchService
{
    protected array $adapters = [];

    public function __construct()
    {
        $this->adapters = [
            'email' => fn($channel, $event) => $this->sendEmail($channel, $event),
            'sms' => fn($channel, $event) => $this->sendSms($channel, $event),
            'satellite' => fn($channel, $event) => $this->sendSatellite($channel, $event),
        ];
    }

    public function registerAdapter(string $channelType, callable $handler): void
    {
        $this->adapters[strtolower($channelType)] = $handler;
    }

    public function dispatch(MarineLab $lab, AlertEvent $event): Collection
    {
        return $lab->alertChannels->map(fn(AlertChannel $channel) => $this->sendViaChannel($channel, $event));
    }

    private function sendViaChannel(AlertChannel $channel, AlertEvent $event): array
    {
        $channelType = Str::lower($channel->channel);

        $adapter = $this->adapters[$channelType] ?? fn($channel, $event) => $this->sendFallback($channel, $event);
        $result = $adapter($channel, $event);
        
        $result['adapter'] = $channelType;
        
        return $result;
    }

    private function sendEmail(AlertChannel $channel, AlertEvent $event): array
    {
        $payload = [
            'to' => $channel->endpoint,
            'subject' => sprintf('Marine Alert • %s', Str::upper($event->event_type)),
            'body' => json_encode($event->payload),
            'headers' => [
                'x-lab' => $channel->marine_lab_id,
                'x-priority' => 'critical',
            ],
        ];

        return [
            'channel' => 'email',
            'status' => 'queued',
            'transport' => 'smtp',
            'payload' => $payload,
        ];
    }

    private function sendSms(AlertChannel $channel, AlertEvent $event): array
    {
        $payload = [
            'number' => $channel->endpoint,
            'message' => sprintf('[%s] %s', $event->event_type, data_get($event->payload, 'summary')),
            'meta' => [
                'window' => now()->addMinutes(5)->toIso8601String(),
                'priority' => data_get($channel->constraints, 'priority', 'high'),
            ],
        ];

        return [
            'channel' => 'sms',
            'status' => 'sent',
            'transport' => 'vonage',
            'payload' => $payload,
        ];
    }

    private function sendSatellite(AlertChannel $channel, AlertEvent $event): array
    {
        $payload = [
            'uplink' => $channel->endpoint,
            'frames' => [
                base64_encode(json_encode($event->payload)),
                base64_encode($event->triggered_at->toIso8601String()),
            ],
            'checksum' => substr(md5($channel->endpoint . $event->event_type), 0, 8),
        ];

        return [
            'channel' => 'satellite',
            'status' => 'transmitted',
            'transport' => 'inmarsat',
            'payload' => $payload,
        ];
    }

    private function sendFallback(AlertChannel $channel, AlertEvent $event): array
    {
        return [
            'channel' => $channel->channel,
            'status' => 'discarded',
            'transport' => 'none',
            'payload' => [
                'endpoint' => $channel->endpoint,
                'event' => $event->event_type,
            ],
        ];
    }
}
