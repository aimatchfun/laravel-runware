<?php

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class RunwareImageInferenceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.imageInference';
    }
}
