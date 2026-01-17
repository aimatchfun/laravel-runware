<?php

/**
 * Exemplo de uso do Laravel Runware com mocks para testes
 * 
 * Este arquivo demonstra como testar o pacote Laravel sem chamar a API real
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AiMatchFun\LaravelRunware\LaravelRunwareFacade;
use AiMatchFun\PhpRunwareSDK\ModelAir;
use AiMatchFun\PhpRunwareSDK\OutputType;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

// Para usar em testes Laravel, você precisaria do Orchestra Testbench
// Este é um exemplo simplificado

echo "=== Exemplo de Mock do Facade Laravel ===\n\n";

// Em um teste real com Orchestra Testbench, você faria:

/*
use Orchestra\Testbench\TestCase;
use AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelRunwareServiceProvider::class];
    }

    public function testWithMock()
    {
        // Opção 1: Mock do Facade (mais simples)
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
            ->andReturn('https://example.com/image.png');

        $result = \Runware::positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }

    public function testWithHttpMock()
    {
        // Opção 2: Mock HTTP Client (mais realista)
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $imageInference = new \Tests\ImageInferenceWrapper('test-api-key');
        $imageInference->setMockClient($mockClient);

        app()->instance('runware', $imageInference);

        $mockHandler->append(new Response(200, [], json_encode([
            'data' => [['imageURL' => 'https://example.com/image.png']]
        ])));

        $result = \Runware::positivePrompt('Test')
            ->negativePrompt('blur')
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
    }
}
*/

echo "Veja os arquivos de teste completos em:\n";
echo "- tests/LaravelRunwareFacadeTest.php\n";
echo "- tests/README.md\n";

