<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;

class ZHelpersTest extends TestCase
{
    /** @test */
    public function localeurl_is_a_wrapper()
    {
        $this->markTestIncomplete();

        app()->setLocale('en');
        $this->assertEquals('http://example.com/en', localeurl('/'));
        $this->assertEquals('http://example.com/en/foobar', localeurl('/foobar'));
        $this->assertEquals('http://example.com', localeurl('/fr', 'nl'));
    }

    /** @test */
    public function localeroute_is_a_wrapper()
    {
        $this->markTestIncomplete();

        app()->setLocale('en');
        Route::get('foo/bar/{slug?}', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/bar', localeroute('foo.custom'));
        $this->assertEquals('http://example.com/fr/foo/bar', localeroute('foo.custom', 'fr'));
        $this->assertEquals('http://example.com/en/foo/bar/cow', localeroute('foo.custom', 'cow'));
    }
}
