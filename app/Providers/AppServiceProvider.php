<?php

namespace App\Providers;

use App\Contracts\OceanDriftRepositoryInterface;
use App\Repositories\OceanDriftRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OceanDriftRepositoryInterface::class, OceanDriftRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Exemplo: Registrar novos adapters de alerta personalizados
        // Descomente para adicionar adapters customizados sem modificar AlertDispatchService
        
        // $alertService = app(\App\Services\AlertDispatchService::class);
        // 
        // // Adapter para Slack
        // $alertService->registerAdapter('slack', function($channel, $event) {
        //     return [
        //         'channel' => 'slack',
        //         'status' => 'posted',
        //         'transport' => 'webhook',
        //         'payload' => [
        //             'webhook_url' => $channel->endpoint,
        //             'text' => sprintf('[%s] %s', $event->event_type, data_get($event->payload, 'summary')),
        //         ],
        //     ];
        // });
    }
}
