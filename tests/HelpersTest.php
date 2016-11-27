<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;

class HelpersTest extends LocaleUrlTest
{
    /** @test */
    public function localeurl_is_a_wrapper()
    {
        app()->setLocale('en');
        $this->assertEquals('http://example.be/en',localeurl('/'));
        $this->assertEquals('http://example.be/en/foobar',localeurl('/foobar'));
        $this->assertEquals('http://example.be',localeurl('/fr','nl'));
    }

    /** @test */
    public function localeroute_is_a_wrapper()
    {
        app()->setLocale('en');
        Route::get('foo/bar/{slug?}',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/en/foo/bar', localeroute('foo.show'));
        $this->assertEquals('http://example.be/fr/foo/bar', localeroute('foo.show','fr'));
        $this->assertEquals('http://example.be/en/foo/bar/cow', localeroute('foo.show', 'cow'));
    }

}
