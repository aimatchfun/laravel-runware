<?php

declare(strict_types=1);

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\ServiceProvider;
use AiMatchFun\PhpRunwareSDK\ImageInference;
use AiMatchFun\PhpRunwareSDK\Inpainting;
use AiMatchFun\PhpRunwareSDK\ImageUpload;
use AiMatchFun\PhpRunwareSDK\PhotoMaker;

class LaravelRunwareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('runware.imageInference', function ($app) {
            return new ImageInference(config('runware.api_key'));
        });

        $this->app->bind('runware.inpainting', function ($app) {
            return new Inpainting(config('runware.api_key'));
        });

        $this->app->bind('runware.imageUpload', function ($app) {
            return new ImageUpload(config('runware.api_key'));
        });

        $this->app->bind('runware.photoMaker', function ($app) {
            return new PhotoMaker(config('runware.api_key'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/runware.php' => config_path('runware.php'),
        ]);
    }
}