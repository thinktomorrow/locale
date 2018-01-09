<?php

namespace Thinktomorrow\Locale\Tests\Integration;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->get('http://example.com');
        $this->refreshBindings();
    }

    /** @test */
    public function localeurl_is_a_wrapper()
    {
        app()->setLocale('en-gb');

        $this->assertEquals('http://example.com/en', localeurl('/'));
        $this->assertEquals('http://example.com/en/foobar', localeurl('/foobar'));
        $this->assertEquals('http://example.com', localeurl('/fr', 'nl'));
    }

    /** @test */
    public function localeroute_is_a_wrapper()
    {
        app()->setLocale('en-gb');
        Route::get('foo/bar/{slug?}', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/bar', localeroute('foo.custom'));
        $this->assertEquals('http://example.com/fr/foo/bar', localeroute('foo.custom', 'fr'));
        $this->assertEquals('http://example.com/en/foo/bar/cow', localeroute('foo.custom', 'cow'));
    }
}
