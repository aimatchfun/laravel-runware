<?php

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class RunwareImageUploadFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.imageUpload';
    }
}
