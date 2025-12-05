<?php

declare(strict_types=1);

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\ServiceProvider;

class LaravelRunwareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('runware', function ($app) {
            return new Runware(config('runware.api_key'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/runware.php' => config_path('runware.php'),
        ]);
    }
}