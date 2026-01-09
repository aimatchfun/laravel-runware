# Laravel Runware

[![Latest Stable Version](https://poser.pugx.org/aimatchfun/laravel-runware/v/stable)](https://packagist.org/packages/aimatchfun/laravel-runware)
[![Total Downloads](https://poser.pugx.org/aimatchfun/laravel-runware/downloads)](https://packagist.org/packages/aimatchfun/laravel-runware)
[![License](https://poser.pugx.org/aimatchfun/laravel-runware/license)](https://packagist.org/packages/aimatchfun/laravel-runware)

A Laravel wrapper for the [PHP Runware SDK](https://github.com/aimatchfun/php-runware-sdk), providing a simple and elegant way to integrate Runware AI services into your Laravel applications.

## Features

- 🚀 Easy integration with Laravel
- 🎨 Full support for Runware AI image generation capabilities
- 🖼️ **Inpainting**: Selective image editing by modifying specific regions
- ⚙️ Simple configuration via environment variables
- 🔧 Service Provider and Facade included
- 📦 Compatible with Laravel 12.x

## Requirements

- PHP ^8.3
- Laravel ^12.0
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

### Using the Facades

The package provides two facades: `RunwareImageInference` and `RunwareInpainting`. You can use them directly:

```php
use RunwareImageInference;
use RunwareInpainting;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

// Example: Generate an image using ImageInference
$imageUrl = RunwareImageInference::positivePrompt('A beautiful sunset over the mountains')
    ->negativePrompt('blur, distortion')
    ->width(512)
    ->height(512)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->run();

// Example: Inpainting
$inpaintedImage = RunwareInpainting::seedImage('image-uuid')
    ->maskImage('mask-uuid')
    ->positivePrompt('a serene beach at sunset')
    ->strength(0.8)
    ->run();
```

### Using Dependency Injection

You can also inject the Runware instance directly:

```php
use AiMatchFun\PhpRunwareSDK\TextToImage;
use AiMatchFun\PhpRunwareSDK\Inpainting;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

class ImageController extends Controller
{
    public function __construct(
        private TextToImage $runware,
        private Inpainting $inpainting
    ) {}

    public function generate()
    {
        $imageUrl = $this->runware
            ->positivePrompt('A futuristic cityscape')
            ->negativePrompt('blur')
            ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
            ->outputType(OutputType::URL)
            ->run();

        return response()->json(['imageUrl' => $imageUrl]);
    }
}
```

### Using the Service Container

```php
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

// ImageInference
$runware = app('runware.imageInference');
$imageUrl = $runware->positivePrompt('A magical forest')
    ->negativePrompt('blur')
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->run();

// Inpainting
$inpainting = app('runware.inpainting');
$inpaintedImage = $inpainting->seedImage('image-uuid')
    ->maskImage('mask-uuid')
    ->positivePrompt('A serene beach at sunset')
    ->negativePrompt('blur, distortion')
    ->strength(0.8)
    ->run();
```

## Available Methods

This package provides access to all methods available in the PHP Runware SDK. Some of the main features include:

- **Text-to-Image**: Create AI-generated images from text prompts
- **Inpainting**: Selective image editing by modifying specific regions of an image
- **Image Enhancement**: Upscale and enhance existing images
- **Background Removal**: Remove backgrounds from images
- **Image-to-Image**: Transform images based on prompts
- **ControlNet**: Advanced image generation with control
- And many more...

For a complete list of available methods and their parameters, please refer to the [PHP Runware SDK documentation](https://github.com/aimatchfun/php-runware-sdk).

## Examples

### Basic Image Generation (Text-to-Image)

```php
use Runware;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

use RunwareImageInference;

$imageUrl = RunwareImageInference::positivePrompt('A serene lake with mountains in the background')
    ->negativePrompt('blur, distortion')
    ->width(1024)
    ->height(1024)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->outputType(OutputType::URL)
    ->numberResults(4)
    ->run();

echo $imageUrl;
```

### Inpainting

Inpainting allows you to selectively edit specific regions of an image by providing a seed image and a mask image:

```php
use RunwareInpainting;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;

$inpaintedImage = RunwareInpainting::seedImage('59a2edc2-45e6-429f-be5f-7ded59b92046') // Image UUID or URL
    ->maskImage('5988e195-8100-4b91-b07c-c7096d0861aa') // Mask UUID or URL
    ->positivePrompt('a serene beach at sunset')
    ->negativePrompt('blur, distortion')
    ->strength(0.8) // Strength of the inpainting effect (0.0 to 1.0)
    ->maskMargin(64) // Extra context pixels around masked region (32-128)
    ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
    ->width(1024)
    ->height(1024)
    ->outputType(OutputType::URL)
    ->run();

echo $inpaintedImage;
```

**Inpainting Parameters:**
- `seedImage()`: The original image you wish to edit (UUID or URL)
- `maskImage()`: Defines the area to be modified (UUID or URL)
- `positivePrompt()`: Describes the desired outcome for the masked area
- `strength()`: Strength of the inpainting effect (0.0 to 1.0, default: 0.8)
- `maskMargin()`: Adds extra context pixels around the masked region (32-128 pixels)

For more details about inpainting, see the [Runware Inpainting Documentation](https://runware.ai/docs/en/image-inference/inpainting).

### Image Upload

You can upload images to Runware for use in various operations:

```php
use AiMatchFun\PhpRunwareSDK\ImageUpload;

// Upload an image from a file path
$imageUpload = app('runware.imageUpload');

$uploadedImage = $imageUpload->uploadImageFromPath('/path/to/image.jpg');

// The response contains the image UUID that can be used in other operations
$imageUUID = $uploadedImage->getImageUUID();

echo $imageUUID; // Use this UUID in inpainting or other image operations
```

**Upload Methods:**
- `uploadImageFromPath()`: Upload an image from a local file path
- The uploaded image can be referenced by its UUID in subsequent operations like inpainting, image enhancement, etc.

For more details about image upload, see the [Runware Image Upload Documentation](https://runware.ai/docs/en/image-inference/image-upload).


## Error Handling

The package throws exceptions for API errors. It's recommended to wrap your calls in try-catch blocks:

```php
use RunwareImageInference;
use AiMatchFun\PhpRunwareSDK\RunwareModel;
use AiMatchFun\PhpRunwareSDK\OutputType;
use Illuminate\Support\Facades\Log;

try {
    $imageUrl = RunwareImageInference::positivePrompt('A beautiful landscape')
        ->negativePrompt('blur')
        ->model(RunwareModel::REAL_DREAM_SDXL_PONY_14)
        ->outputType(OutputType::URL)
        ->run();
} catch (\Exception $e) {
    // Handle the error
    Log::error('Runware API error: ' . $e->getMessage());
}
```

## Testing

When writing tests for code that uses this package, you can mock the Runware facade:

```php
use RunwareImageInference;
use RunwareInpainting;

// Mock ImageInference
RunwareImageInference::shouldReceive('positivePrompt')
    ->once()
    ->with('Test prompt')
    ->andReturnSelf();

RunwareImageInference::shouldReceive('run')
    ->once()
    ->andReturn('https://example.com/test-image.jpg');

// Mock Inpainting
RunwareInpainting::shouldReceive('seedImage')
    ->once()
    ->with('image-uuid')
    ->andReturnSelf();

RunwareInpainting::shouldReceive('run')
    ->once()
    ->andReturn('https://example.com/inpainted-image.jpg');
```

For more detailed testing examples, see the [tests documentation](tests/README.md).

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
