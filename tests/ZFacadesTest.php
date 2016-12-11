<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Facades\LocaleFacade;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;

class ZFacadesTest extends LocaleUrlTest
{
    /** @test */
    public function locale_facade()
    {
        app()->setLocale('en');

        $this->assertEquals('nl',LocaleFacade::get('nl'));
        $this->assertEquals('en',LocaleFacade::getSlug());
    }

    /** @test */
    public function localeurl_facade()
    {
        app()->setLocale('en');
        Route::get('foo/bar/{slug?}',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.com/en/foo/bar', LocaleUrlFacade::route('foo.show'));
        $this->assertEquals('http://example.com/en',LocaleUrlFacade::to('/'));
    }

}
