<?php

namespace Daavelar\LaravelRunware;

use Illuminate\Support\Facades\Facade;

class LaravelRunwareFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runware';
    }
}