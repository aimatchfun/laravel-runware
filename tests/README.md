# Guia de Testes - Laravel Runware

Este guia explica como testar o pacote Laravel Runware sem fazer chamadas reais à API da Runware.

## Estrutura de Testes

Os testes usam `Orchestra Testbench` para simular um ambiente Laravel completo.

## Como Usar

### Opção 1: Mock do Facade (Mais Simples)

```php
use Runware;
use AiMatchFun\PhpRunwareSDK\RunwareResponse;
use AiMatchFun\PhpRunwareSDK\RunwareImageResponse;

// Mockar métodos do facade
\Runware::shouldReceive('positivePrompt')
    ->once()
    ->with('A beautiful sunset')
    ->andReturnSelf();

\Runware::shouldReceive('negativePrompt')
    ->once()
    ->with('blur')
    ->andReturnSelf();

$mockResponse = new RunwareResponse([
    new RunwareImageResponse(
        taskType: 'imageInference',
        imageUUID: 'test-uuid',
        taskUUID: 'test-task-uuid',
        imageURL: 'https://example.com/image.png'
    )
]);

\Runware::shouldReceive('run')
    ->once()
    ->andReturn($mockResponse);

$result = \Runware::positivePrompt('A beautiful sunset')
    ->negativePrompt('blur')
    ->run();

// $result é uma instância de RunwareResponse
$imageURL = $result->first()?->imageURL; // 'https://example.com/image.png'
```

### Opção 2: Mock do Serviço com HTTP Client Mockado

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\ImageInferenceWrapper;

// Criar mock handler
$mockHandler = new MockHandler();
$handlerStack = HandlerStack::create($mockHandler);
$mockClient = new Client(['handler' => $handlerStack]);

// Criar instância com mock
$imageInference = new ImageInferenceWrapper('test-api-key');
$imageInference->setMockClient($mockClient);

// Registrar no container
app()->instance('runware', $imageInference);

// Configurar resposta mockada
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

        // Usar o facade normalmente
        $result = \Runware::positivePrompt('A beautiful sunset')
            ->negativePrompt('blur')
            ->run();
        
        // $result é uma instância de RunwareResponse
        $imageURL = $result->first()?->imageURL;
```

### Opção 3: Usando Dependency Injection

```php
use AiMatchFun\PhpRunwareSDK\ImageInference;
use Tests\ImageInferenceWrapper;

// Em um controller ou service
class ImageController extends Controller
{
    public function __construct(
        private ImageInference $runware
    ) {}

    public function generate()
    {
        // Em testes, você pode injetar o ImageInferenceWrapper mockado
        $result = $this->runware
            ->positivePrompt('A beautiful sunset')
            ->run();
        
        // $result é uma instância de RunwareResponse
        return response()->json(['image' => $result->first()?->imageURL]);
    }
}

// No teste
$mockHandler = new MockHandler();
// ... configurar mock ...

$imageInference = new ImageInferenceWrapper('test-api-key');
$imageInference->setMockClient($mockClient);

$controller = new ImageController($imageInference);
$response = $controller->generate();
```

## Exemplo Completo de Teste

```php
<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider;
use AiMatchFun\LaravelRunware\LaravelRunwareFacade;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\ImageInferenceWrapper;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelRunwareServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['Runware' => LaravelRunwareFacade::class];
    }

    public function testImageGeneration()
    {
        // Configurar mock
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $imageInference = new ImageInferenceWrapper('test-api-key');
        $imageInference->setMockClient($mockClient);

        app()->instance('runware', $imageInference);

        // Resposta mockada
        $mockHandler->append(new Response(200, [], json_encode([
            'data' => [['imageURL' => 'https://example.com/image.png']]
        ])));

        // Testar
        $result = \Runware::positivePrompt('Test')
            ->negativePrompt('blur')
            ->run();

        // $result é uma instância de RunwareResponse
        $this->assertInstanceOf(\AiMatchFun\PhpRunwareSDK\RunwareResponse::class, $result);
        $this->assertEquals('https://example.com/image.png', $result->first()?->imageURL);
    }
}
```

## Executando os Testes

```bash
# Executar todos os testes
vendor/bin/phpunit

# Executar um teste específico
vendor/bin/phpunit tests/LaravelRunwareFacadeTest.php

# Com cobertura
vendor/bin/phpunit --coverage-text
```

## Configuração

Certifique-se de ter configurado o arquivo `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="Tests">
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## Notas

1. O `ImageInferenceWrapper` deve estar disponível nos testes. Você pode copiá-lo do pacote `php-runware-sdk` ou criar um helper compartilhado.

2. Use `Orchestra Testbench` para testar pacotes Laravel sem uma aplicação completa.

3. O mock do facade funciona bem para testes rápidos, mas o mock HTTP é mais realista para testes de integração.

