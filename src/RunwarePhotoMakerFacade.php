<?php

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class RunwarePhotoMakerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware.photoMaker';
    }
}
