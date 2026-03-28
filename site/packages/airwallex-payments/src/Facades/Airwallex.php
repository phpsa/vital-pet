<?php

namespace Vital\Airwallex\Facades;

use Illuminate\Support\Facades\Facade;

class Airwallex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar:airwallex';
    }
}
