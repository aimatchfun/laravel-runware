<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\LaravelRunware\LaravelRunwareFacade;
use AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider;
use AiMatchFun\PhpRunwareSDK\ModelAir;
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

    /**
     * Mocka o serviço runware com um HTTP client mockado
     */
    private function mockRunwareService(): MockHandler
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Criar uma instância de TextToImage com mock client
        $textToImage = new TextToImageWrapper('test-api-key');
        $textToImage->setMockClient($mockClient);

        // Registrar no container
        $this->app->instance('runware', $textToImage);

        return $mockHandler;
    }

    public function testFacadeCanBeResolved(): void
    {
        $this->mockRunwareService();

        $runware = \Runware::getFacadeRoot();
        
        $this->assertInstanceOf(TextToImage::class, $runware);
    }

    public function testFacadeCanCallMethods(): void
    {
        $mockHandler = $this->mockRunwareService();

        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageInference',
                    'taskUUID' => 'test-task-uuid',
                    'imageUUID' => 'test-image-uuid',
                    'imageURL' => 'https://example.com/image.png',
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        $result = \Runware::positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->width(512)
            ->height(512)
            ->modelAir(ModelAir::REAL_DREAM_SDXL_PONY_14->value)
            ->outputType(OutputType::URL)
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
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

/**
 * Classe wrapper testável (mesma do php-runware-sdk)
 */
class TextToImageWrapper extends TextToImage
{
    private ?Client $mockClient = null;

    public function setMockClient(Client $client): void
    {
        $this->mockClient = $client;
    }

    /**
     * Sobrescreve o método run para usar o mock client
     * Como o método post é privado na classe pai, precisamos usar reflection
     */
    public function run(): string
    {
        if ($this->mockClient === null) {
            // Se não há mock, usar comportamento padrão
            return parent::run();
        }

        // Usar reflection para acessar métodos e propriedades privadas
        $reflection = new \ReflectionClass(\AiMatchFun\PhpRunwareSDK\TextToImage::class);
        
        // Acessar propriedades privadas da classe pai
        $apiUrlProperty = $reflection->getProperty('apiUrl');
        $apiUrlProperty->setAccessible(true);
        $apiUrl = $apiUrlProperty->getValue($this);

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $apiKey = $apiKeyProperty->getValue($this);

        // Criar closure que usa nosso mock client
        $mockClient = $this->mockClient;

        // Criar função post mockada
        $mockPost = function(array $data) use ($mockClient, $apiUrl, $apiKey) {
            try {
                $response = $mockClient->post($apiUrl, [
                    'json' => [$data],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $apiKey
                    ]
                ]);

                return $response->getBody()->getContents();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                throw new \Exception("Runware API Error: " . $e->getResponse()->getBody()->getContents());
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                throw new \Exception("Runware Server Error: " . $e->getResponse()->getBody()->getContents());
            } catch (\Exception $e) {
                throw new \Exception("Error connecting to Runware API: " . $e->getMessage());
            }
        };

        // Usar o método mountRequestBody da classe pai
        $mountRequestBodyMethod = $reflection->getMethod('mountRequestBody');
        $mountRequestBodyMethod->setAccessible(true);
        $requestBody = $mountRequestBodyMethod->invoke($this);

        // Chamar nosso método post mockado
        $response = $mockPost($requestBody);

        // Usar handleResponse da classe pai
        $handleResponseMethod = $reflection->getMethod('handleResponse');
        $handleResponseMethod->setAccessible(true);
        
        return $handleResponseMethod->invoke($this, $response);
    }
}

