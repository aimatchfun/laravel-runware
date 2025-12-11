<?php

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class RunwareInpaintingFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.inpainting';
    }
}
