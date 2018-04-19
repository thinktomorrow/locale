<?php

namespace Thinktomorrow\Locale\Facades;

use Illuminate\Support\Facades\Facade;
use Thinktomorrow\Locale\LocaleUrl;

class LocaleUrlFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return LocaleUrl::class;
    }
}
