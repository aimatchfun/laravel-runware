<?php

namespace AiMatchFun\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class LaravelRunwareFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware';
    }
}