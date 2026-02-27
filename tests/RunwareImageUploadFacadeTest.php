<?php

declare(strict_types=1);

namespace Tests;

use AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider;
use AiMatchFun\LaravelRunware\Facades\RunwareImageUpload;
use AiMatchFun\PhpRunwareSDK\ImageUpload;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class RunwareImageUploadFacadeTest extends TestbenchTestCase
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
            'RunwareImageUpload' => RunwareImageUpload::class,
        ];
    }

    public function testImageUploadFacadeCanBeResolved(): void
    {
        $imageUpload = $this->app->make('runware.imageUpload');

        $this->assertInstanceOf(ImageUpload::class, $imageUpload);
    }

    public function testImageUploadFacadeCanBeAccessedVisFacade(): void
    {
        $imageUpload = \RunwareImageUpload::getFacadeRoot();

        $this->assertInstanceOf(ImageUpload::class, $imageUpload);
    }

    public function testImageUploadCanUploadFromUrl(): void
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        // Mock response from Runware API
        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageUpload',
                    'imageUUID' => 'test-image-uuid-12345',
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        // Create a wrapper instance with mock client
        $imageUpload = new ImageUploadWrapper('test-api-key');
        $imageUpload->setMockClient($mockClient);

        $result = $imageUpload
            ->uploadFromURL('https://example.com/image.png')
            ->run();

        $this->assertEquals('test-image-uuid-12345', $result);
    }

    public function testImageUploadCanUploadFromBase64(): void
    {
        $mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($mockHandler);
        $mockClient = new Client(['handler' => $handlerStack]);

        $mockResponse = new Response(200, [], json_encode([
            'data' => [
                [
                    'taskType' => 'imageUpload',
                    'imageUUID' => 'test-image-uuid-base64',
                ]
            ]
        ]));

        $mockHandler->append($mockResponse);

        $imageUpload = new ImageUploadWrapper('test-api-key');
        $imageUpload->setMockClient($mockClient);

        // Create a temporary image file for testing
        $tempFile = sys_get_temp_dir() . '/test_image_' . uniqid() . '.png';
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        file_put_contents($tempFile, base64_decode($base64Image));

        try {
            $result = $imageUpload
                ->uploadFromLocalPath($tempFile)
                ->run();

            $this->assertEquals('test-image-uuid-base64', $result);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        $this->assertEquals('test-image-uuid-base64', $result);
    }

    public function testImageUploadThrowsExceptionWhenImagePathIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $imageUpload = $this->app->make('runware.imageUpload');
        $imageUpload->uploadFromLocalPath('')->run();
    }

    public function testImageUploadThrowsExceptionWhenImageURLIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $imageUpload = $this->app->make('runware.imageUpload');
        $imageUpload->uploadFromURL('')->run();
    }

    public function testImageUploadThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $imageUpload = $this->app->make('runware.imageUpload');
        $imageUpload->uploadFromLocalPath('/nonexistent/file.png')->run();
    }

    public function testImageUploadCanBeMocked(): void
    {
        \RunwareImageUpload::shouldReceive('uploadFromURL')
            ->once()
            ->with('https://example.com/image.png')
            ->andReturnSelf();

        \RunwareImageUpload::shouldReceive('run')
            ->once()
            ->andReturn('mocked-uuid-12345'); // ImageUpload ainda retorna string (imageUUID)

        $result = \RunwareImageUpload::uploadFromURL('https://example.com/image.png')
            ->run();

        $this->assertEquals('mocked-uuid-12345', $result);
    }
}

/**
 * Classe wrapper testável para ImageUpload
 */
class ImageUploadWrapper extends ImageUpload
{
    private ?Client $mockClient = null;

    public function setMockClient(Client $client): void
    {
        $this->mockClient = $client;
    }

    public function run(): string
    {
        if ($this->mockClient === null) {
            return parent::run();
        }

        $reflection = new \ReflectionClass(ImageUpload::class);

        $imageProperty = $reflection->getProperty('image');
        $imageProperty->setAccessible(true);
        $image = $imageProperty->getValue($this);

        if (empty($image)) {
            throw new \InvalidArgumentException("Image is required for upload");
        }

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $apiKey = $apiKeyProperty->getValue($this);

        $apiUrlProperty = $reflection->getProperty('apiUrl');
        $apiUrlProperty->setAccessible(true);
        $apiUrl = $apiUrlProperty->getValue($this);

        $mountRequestBodyMethod = $reflection->getMethod('mountRequestBody');
        $mountRequestBodyMethod->setAccessible(true);
        $requestBody = $mountRequestBodyMethod->invoke($this);

        $mockClient = $this->mockClient;

        $mockPost = function (array $data) use ($mockClient, $apiUrl, $apiKey) {
            try {
                $response = $mockClient->post($apiUrl, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $apiKey,
                    ],
                    'body' => json_encode([$data]),
                ]);

                return $response->getBody()->getContents();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                throw new \Exception("Runware API Error: " . $e->getMessage());
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                throw new \Exception("Runware Server Error: " . $e->getMessage());
            }
        };

        $response = $mockPost($requestBody);

        $handleResponseMethod = $reflection->getMethod('handleResponse');
        $handleResponseMethod->setAccessible(true);

        return $handleResponseMethod->invoke($this, $response);
    }
}
