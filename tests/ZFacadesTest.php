<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Facades\ScopeFacade;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;

class ZFacadesTest extends TestCase
{
    /** @test */
    public function locale_facade()
    {
        $this->refreshBindings('nl','http://example.com');
        $this->get('http://example.com/en');

        $this->assertEquals('en-gb', ScopeFacade::detect()->getScope()->active());
        $this->assertEquals('en', ScopeFacade::detect()->getScope()->activeSegment());
    }

    /** @test */
    public function localeurl_facade()
    {
        app()->setLocale('en');
        Route::get('foo/bar/{slug?}', ['as' => 'foo.show', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/en/foo/bar', LocaleUrlFacade::route('foo.show'));
        $this->assertEquals('http://example.com/en', LocaleUrlFacade::to('/'));
    }
}
