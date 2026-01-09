<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\LaravelRunware\LaravelRunwareFacade;
use AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider;
use AiMatchFun\PhpRunwareSDK\OutputFormat;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\TextToImage;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\TestCase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class LaravelRunwareFacadeTest extends TestbenchTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar a API key de teste
        config(['runware.api_key' => 'test-api-key']);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelRunwareServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Runware' => LaravelRunwareFacade::class,
        ];
    }

    public function testFacadeCanBeResolved(): void
    {
        $runware = $this->app->make('runware.imageInference');

        $this->assertInstanceOf(TextToImage::class, $runware);
    }

    public function testFacadeCanCallMethods(): void
    {
        $result = \Runware::positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->width(512)
            ->height(512)
            ->model('civitai:618692@691639')
            ->outputType(OutputType::URL);

        // Just verify the facade returns a TextToImage instance with the methods callable
        $this->assertInstanceOf(TextToImage::class, $result);
    }

    public function testFacadeCanBeMocked(): void
    {
        \Runware::shouldReceive('positivePrompt')
            ->once()
            ->with('A beautiful sunset')
            ->andReturnSelf();

        \Runware::shouldReceive('negativePrompt')
            ->once()
            ->with('blur')
            ->andReturnSelf();

        \Runware::shouldReceive('run')
            ->once()
            ->andReturn('https://example.com/mocked-image.png');

        $result = \Runware::positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();

        $this->assertEquals('https://example.com/mocked-image.png', $result);
    }
}

