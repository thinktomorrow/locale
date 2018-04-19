<?php

namespace Thinktomorrow\Locale\Facades;

use Illuminate\Support\Facades\Facade;

class ScopeFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'tt-locale-scope';
    }
}
