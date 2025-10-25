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
    public function boot(): void {}
}
