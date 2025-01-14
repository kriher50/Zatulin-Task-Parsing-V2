<?php

namespace App\Providers;

use App\Contracts\NewsServiceInterface;
use App\Services\NewsService;
use App\Services\NewsSource\LentaNewsSource;
use App\Services\NewsSource\RiaNewsSource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsServiceInterface::class, NewsService::class);
        $this->app->singleton(LentaNewsSource::class);
        $this->app->singleton(RiaNewsSource::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
