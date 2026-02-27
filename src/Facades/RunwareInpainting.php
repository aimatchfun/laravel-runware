<?php

namespace AiMatchFun\LaravelRunware\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \AiMatchFun\PhpRunwareSDK\Inpainting
 */
class RunwareInpainting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.inpainting';
    }
}
