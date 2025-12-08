# Guia de Testes - Laravel Runware

Este guia explica como testar o pacote Laravel Runware sem fazer chamadas reais à API da Runware.

## Estrutura de Testes

Os testes usam `Orchestra Testbench` para simular um ambiente Laravel completo.

## Como Usar

### Opção 1: Mock do Facade (Mais Simples)

```php
use Runware;

// Mockar métodos do facade
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
```

### Opção 2: Mock do Serviço com HTTP Client Mockado

```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TextToImageWrapper;

// Criar mock handler
$mockHandler = new MockHandler();
$handlerStack = HandlerStack::create($mockHandler);
$mockClient = new Client(['handler' => $handlerStack]);

// Criar instância com mock
$textToImage = new TextToImageWrapper('test-api-key');
$textToImage->setMockClient($mockClient);

// Registrar no container
app()->instance('runware', $textToImage);

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
```

### Opção 3: Usando Dependency Injection

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use Tests\TextToImageWrapper;

// Em um controller ou service
class ImageController extends Controller
{
    public function __construct(
        private TextToImage $runware
    ) {}

    public function generate()
    {
        // Em testes, você pode injetar o TextToImageWrapper mockado
        $result = $this->runware
            ->positivePrompt('A beautiful sunset')
            ->run();
        
        return response()->json(['image' => $result]);
    }
}

// No teste
$mockHandler = new MockHandler();
// ... configurar mock ...

$textToImage = new TextToImageWrapper('test-api-key');
$textToImage->setMockClient($mockClient);

$controller = new ImageController($textToImage);
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
use Tests\TextToImageWrapper;

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

        $textToImage = new TextToImageWrapper('test-api-key');
        $textToImage->setMockClient($mockClient);

        app()->instance('runware', $textToImage);

        // Resposta mockada
        $mockHandler->append(new Response(200, [], json_encode([
            'data' => [['imageURL' => 'https://example.com/image.png']]
        ])));

        // Testar
        $result = \Runware::positivePrompt('Test')
            ->negativePrompt('blur')
            ->run();

        $this->assertEquals('https://example.com/image.png', $result);
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

1. O `TextToImageWrapper` deve estar disponível nos testes. Você pode copiá-lo do pacote `php-runware-sdk` ou criar um helper compartilhado.

2. Use `Orchestra Testbench` para testar pacotes Laravel sem uma aplicação completa.

3. O mock do facade funciona bem para testes rápidos, mas o mock HTTP é mais realista para testes de integração.

