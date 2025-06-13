<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class AppRouteProvider extends RouteServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $twitterActionPrefix = Config::get('twitter.controller.action_prefix');
        Route::macro('twitterFullUrlRoutes', function () use ($twitterActionPrefix) {
            if ($twitterActionPrefix) {
                $routes = $this->getRoutes()->getRoutes();
                $this->name('twitter-url.')->prefix($twitterActionPrefix)->group(function () use ($routes) {
                    foreach ($routes as $route) {
                        if (str_starts_with($route->getName(), 'twitter.')) {
                            $twitterPath = explode('twitter/', $route->uri());
                            $this->get('/' . ($twitterPath[1] ?? null), $route->action['controller']);
                        }
                    }
                });
            }
        });
    }
}
