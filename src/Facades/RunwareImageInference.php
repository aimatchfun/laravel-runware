<?php

namespace AiMatchFun\LaravelRunware\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \AiMatchFun\PhpRunwareSDK\ImageInference
 */
class RunwareImageInference extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.imageInference';
    }
}
