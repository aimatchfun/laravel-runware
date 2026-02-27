<?php

namespace AiMatchFun\LaravelRunware\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \AiMatchFun\PhpRunwareSDK\PhotoMaker
 */
class RunwarePhotoMaker extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.photoMaker';
    }
}
