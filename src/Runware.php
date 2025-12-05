<?php

declare(strict_types=1);

namespace AiMatchFun\LaravelRunware;

use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\OutputType;
use AiMatchFun\PhpRunwareSDK\OutputFormat;

final class Runware
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Generate an image using the Runware API
     *
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>|string
     */
    public function imageInference(array $params): array|string
    {
        $textToImage = new TextToImage($this->apiKey);

        if (isset($params['positivePrompt'])) {
            $textToImage->positivePrompt($params['positivePrompt']);
        }

        if (isset($params['negativePrompt'])) {
            $textToImage->negativePrompt($params['negativePrompt']);
        }

        if (isset($params['model'])) {
            $textToImage->modelAir($params['model']);
        }

        if (isset($params['height'])) {
            $textToImage->height((int) $params['height']);
        }

        if (isset($params['width'])) {
            $textToImage->width((int) $params['width']);
        }

        if (isset($params['steps'])) {
            $textToImage->steps((int) $params['steps']);
        }

        if (isset($params['CFGScale'])) {
            $textToImage->cfgScale((float) $params['CFGScale']);
        }

        if (isset($params['numberResults'])) {
            $textToImage->numberResults((int) $params['numberResults']);
        }

        if (isset($params['outputFormat'])) {
            $outputFormat = match (strtoupper($params['outputFormat'])) {
                'PNG' => OutputFormat::PNG,
                'JPG', 'JPEG' => OutputFormat::JPG,
                'WEBP' => OutputFormat::WEBP,
                default => OutputFormat::PNG,
            };
            $textToImage->outputFormat($outputFormat);
        }

        $textToImage->outputType(OutputType::URL);

        $result = $textToImage->run();

        // O método run() retorna uma string (URL) quando outputType é URL
        // Precisamos retornar no formato esperado pelo RunwareService
        if (is_string($result)) {
            return [['imageURL' => $result]];
        }

        // Se já for um array, retorna como está
        if (is_array($result)) {
            return $result;
        }

        return [];
    }
}

