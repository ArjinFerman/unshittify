<?php

namespace App\Providers;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Twitter\Services\TwitterService;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TwitterService::class, function ($app) {
            return new TwitterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
