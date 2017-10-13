<?php

namespace Thinktomorrow\Locale\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleUrlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshBindings();
    }

    /** @test */
    public function it_can_create_an_url_with_locale_segment()
    {

        $urls = [
            '/foo/bar'                          => 'http://example.com/fr/foo/bar',
            'foo/bar'                           => 'http://example.com/fr/foo/bar',
            ''                                  => 'http://example.com/fr',
            'http://example.com'                => 'http://example.com/fr',
            'http://example.com/foo/bar'        => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/foo/bar'         => 'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar'    => 'https://example.com/fr/foo/bar',
            'https://example.com/es/foo/bar'    => 'https://example.com/fr/es/foo/bar', // Unknown locale segment for current scope is left untouched
            'https://example.com/foo/bar#index' => 'https://example.com/fr/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'BE_fr'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'fr').' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function it_can_create_an_url_with_hidden_locale_segment()
    {
        $urls = [
            '/foo/bar'                          => 'http://example.com/foo/bar',
            'foo/bar'                           => 'http://example.com/foo/bar',
            ''                                  => 'http://example.com',
            'http://example.com'                => 'http://example.com',
            'http://example.com/foo/bar'        => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/foo/bar?s=q',
            'http://example.nl/foo/bar'         => 'http://example.nl/foo/bar',
            'https://example.com/nl/foo/bar'    => 'https://example.com/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'FR_fr'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'nl').' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function it_can_create_a_named_route_with_locale_segment()
    {
        // In fact routekey is taken from the translation files
        Route::get('foo/bar/{slug?}', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foo.custom', 'BE_fr'));
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foo.custom', 'fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foo.custom', 'FR_fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foo.custom', '/'));
    }

    /** @test */
    public function to_accept_parameter_as_string()
    {
        // In fact routekey is taken from the translation files
        Route::get('foo/bar/{slug}', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/fr/foo/bar/crazy', $this->localeUrl->route('foo.custom', 'fr', 'crazy'));
    }

    /** @test */
    public function it_can_create_a_named_route_with_nonlocale_segment()
    {
        app()->setLocale('en-gb');
        Route::get('foo/bar/{slug?}', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->route('foo.custom'));
        $this->assertEquals('http://example.com/en/foo/bar/cow', $this->localeUrl->route('foo.custom', 'cow'));
    }

    /** @test */
    public function it_can_create_a_named_route_with_first_dynamic_segment()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar', $this->localeUrl->route('foo.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('foo.custom', 'fr', ['color' => 'blue']));

        app()->setLocale('BE-nl');
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('foo.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('foo.custom', 'nl', ['color' => 'blue']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('foo.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('foo.custom', 'FR_fr', ['color' => 'blue']));


    }

    /** @test */
    public function on_duplicate_locale_the_last_one_is_used()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('foo.custom', 'en', ['locale_slug' => 'fr', 'color' => 'blue']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments_for_hidden_locale()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_translated_route_with_prefixed_route()
    {
        $this->get('http://foobar.com');
        $this->refreshBindings('Foobar');

        Route::group(['prefix' => app(Detect::class)->detect()->getScope()->activeSegment()], function () {
            Route::get('/foo/bar/{color}', ['as' => 'foo.custom', 'uses' => function () {
            }]);
        });

        $this->assertEquals('http://foobar.com/foo/bar/blue', $this->localeUrl->route('foo.custom', ['locale_slug' => null, 'color' => 'blue']));
        $this->assertEquals('http://foobar.com/foo/bar/blue', $this->localeUrl->route('foo.custom', 'Foobar', ['color' => 'blue']));
        $this->assertEquals('http://foobar.com/foo/bar/blue', $this->localeUrl->route('foo.custom', ['locale_slug' => '/', 'color' => 'blue']));
        $this->assertEquals('http://foobar.com/en/foo/bar/blue', $this->localeUrl->route('foo.custom', ['locale_slug' => 'en', 'color' => 'blue']));

        // Passed locale not present in current scope
        $this->assertEquals('http://foobar.com/foo/bar/blue?de', $this->localeUrl->route('foo.custom', ['locale_slug' => 'de', 'color' => 'blue']));
    }

}
