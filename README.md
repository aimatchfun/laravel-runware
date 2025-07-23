# Laravel Runware

[![Latest Stable Version](https://poser.pugx.org/aimatchfun/laravel-runware/v/stable)](https://packagist.org/packages/aimatchfun/laravel-runware)
[![Total Downloads](https://poser.pugx.org/aimatchfun/laravel-runware/downloads)](https://packagist.org/packages/aimatchfun/laravel-runware)
[![License](https://poser.pugx.org/aimatchfun/laravel-runware/license)](https://packagist.org/packages/aimatchfun/laravel-runware)

A Laravel wrapper for the [PHP Runware SDK](https://github.com/aimatchfun/php-runware-sdk), providing a simple and elegant way to integrate Runware AI services into your Laravel applications.

## Features

- 🚀 Easy integration with Laravel
- 🎨 Full support for Runware AI image generation capabilities
- ⚙️ Simple configuration via environment variables
- 🔧 Service Provider and Facade included
- 📦 Compatible with Laravel 11.x and 12.x

## Requirements

- PHP ^8.0
- Laravel ^11.0 or ^12.0
- A valid Runware API key

## Installation

You can install the package via Composer:

```bash
composer require aimatchfun/laravel-runware
```

## Configuration

After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="AiMatchFun\LaravelRunware\LaravelRunwareServiceProvider"
```

This will create a `config/runware.php` configuration file in your application.

Add your Runware API key to your `.env` file:

```env
RUNWARE_API_KEY=your-api-key-here
```

## Usage

### Using the Facade

You can use the Runware facade to access all the methods from the PHP Runware SDK. The package automatically registers a `Runware` alias, so you can use it directly:

```php
use Runware;
// or
use AiMatchFun\LaravelRunware\LaravelRunwareFacade as Runware;

// Example: Generate an image
$response = Runware::imageInference([
    'positivePrompt' => 'A beautiful sunset over the mountains',
    'model' => 'runware:100@1',
    'numberResults' => 1,
    'outputFormat' => 'PNG',
    'height' => 512,
    'width' => 512,
]);
```

### Using Dependency Injection

You can also inject the Runware instance directly:

```php
use AiMatchFun\PhpRunwareSDK\Runware;

class ImageController extends Controller
{
    public function __construct(
        private Runware $runware
    ) {}

    public function generate()
    {
        $response = $this->runware->imageInference([
            'positivePrompt' => 'A futuristic cityscape',
            'model' => 'runware:100@1',
            'numberResults' => 1,
        ]);

        return response()->json($response);
    }
}
```

### Using the Service Container

```php
$runware = app('runware');

$response = $runware->imageInference([
    'positivePrompt' => 'A magical forest',
    'model' => 'runware:100@1',
]);
```

## Available Methods

This package provides access to all methods available in the PHP Runware SDK. Some of the main features include:

- **Image Generation**: Create AI-generated images from text prompts
- **Image Enhancement**: Upscale and enhance existing images
- **Background Removal**: Remove backgrounds from images
- **Image-to-Image**: Transform images based on prompts
- **ControlNet**: Advanced image generation with control
- And many more...

For a complete list of available methods and their parameters, please refer to the [PHP Runware SDK documentation](https://github.com/aimatchfun/php-runware-sdk).

## Examples

### Basic Image Generation

```php
use Runware;

$images = Runware::imageInference([
    'positivePrompt' => 'A serene lake with mountains in the background',
    'negativePrompt' => 'blur, distortion',
    'model' => 'runware:100@1',
    'numberResults' => 4,
    'outputFormat' => 'JPEG',
    'height' => 1024,
    'width' => 1024,
]);

foreach ($images as $image) {
    // Process each generated image
    echo $image['imageURL'];
}
```

### Image Upscaling

```php
use Runware;

$upscaled = Runware::imageUpscale([
    'inputImage' => 'https://example.com/image.jpg',
    'upscaleFactor' => 2,
]);
```

### Background Removal

```php
use Runware;

$result = Runware::imageBackgroundRemoval([
    'inputImage' => 'https://example.com/image-with-background.jpg',
]);
```

## Error Handling

The package throws exceptions for API errors. It's recommended to wrap your calls in try-catch blocks:

```php
use Runware;
use Illuminate\Support\Facades\Log;

try {
    $response = Runware::imageInference([
        'positivePrompt' => 'A beautiful landscape',
        'model' => 'runware:100@1',
    ]);
} catch (\Exception $e) {
    // Handle the error
    Log::error('Runware API error: ' . $e->getMessage());
}
```

## Testing

When writing tests for code that uses this package, you can mock the Runware facade:

```php
use Runware;

Runware::shouldReceive('imageInference')
    ->once()
    ->with([
        'positivePrompt' => 'Test prompt',
        'model' => 'runware:100@1',
    ])
    ->andReturn([
        ['imageURL' => 'https://example.com/test-image.jpg']
    ]);
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- [Diego Avelar](https://github.com/daavelar)
- [All Contributors](../../contributors)

## Support

If you encounter any issues or have questions, please [open an issue](https://github.com/aimatchfun/laravel-runware/issues) on GitHub.

## See Also

- [PHP Runware SDK](https://github.com/aimatchfun/php-runware-sdk) - The underlying PHP SDK this package wraps
- [Runware Documentation](https://docs.runware.ai/) - Official Runware API documentation
