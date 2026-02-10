<?php

namespace Modules\WebService\App\Facades;

use Illuminate\Support\Facades\Facade;

class SeniorSoap extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'senior.soap';
    }
}
