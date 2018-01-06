<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\DetectLocaleAndScope;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleUrlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshBindings();

        Route::get('foo/bar/{slug}', ['as' => 'foo.custom', 'uses' => function () {}]);
        Route::get('foo/bar', ['as' => 'foobar.sample', 'uses' => function () {}]);
        Route::get('{color}/foo/bar', ['as' => 'bar.custom', 'uses' => function () {}]);
    }

    /** @test */
    public function when_localizing_an_url_it_keeps_all_uri_segments_intact()
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
    public function when_localizing_an_url_with_default_locale_it_keeps_all_uri_segments_intact()
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
    public function it_can_localize_a_named_route()
    {
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foobar.sample', 'BE_fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foobar.sample', 'FR_fr'));
    }

    /** @test */
    public function it_halts_execution_if_required_parameter_is_missing()
    {
        $this->expectException(UrlGenerationException::class);

        $this->localeUrl->route('foo.custom', 'FR_fr');
    }

    /** @test */
    public function if_locale_segment_is_passed_instead_of_the_locale_it_can_still_be_localized()
    {
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foobar.sample', 'fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foobar.sample', '/'));
    }

    /** @test */
    public function an_uri_segment_can_be_passed()
    {
        $this->assertEquals('http://example.com/fr/foo/bar/crazy', $this->localeUrl->route('foo.custom', 'fr', 'crazy'));
        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('bar.custom', 'fr', ['color' => 'blue']));
    }

    /** @test */
    public function if_custom_uri_segment_can_be_passed_as_second_parameter()
    {
        $this->assertEquals('http://example.com/foo/bar/cow', $this->localeUrl->route('foo.custom', 'cow'));
        $this->assertEquals('http://example.com/en/foo/bar/cow', $this->localeUrl->route('foo.custom', 'cow', ['locale_slug' => 'en']));

        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
    }

    /** @test */
    public function it_can_create_a_named_route_where_first_segment_is_dynamic()
    {
        app()->setLocale('BE-nl');
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('bar.custom', 'nl', ['color' => 'blue']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('bar.custom', 'FR_fr', ['color' => 'blue']));
    }

    /** @test */
    public function in_case_of_duplicate_locale_parameters_the_one_last_passed_is_used()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('foo.custom', 'en', ['locale_slug' => 'fr', 'color' => 'blue']));
    }

    /** @test */
    public function first_dynamic_url_segment_is_replaced()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments_for_hidden_locale()
    {
        Route::get('{color}/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_translated_prefixed_route()
    {
        $this->get('http://foobar.com');
        $this->refreshBindings('Foobar');

        Route::group(['prefix' => app(DetectLocaleAndScope::class)->detectLocale()->getScope()->activeSegment()], function () {
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

    /** @test */
    public function localeurl_facade()
    {
        app()->setLocale('en-gb');
        Route::get('foobar', ['as' => 'foobar.sample', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/en/foobar', LocaleUrlFacade::route('foobar.sample'));
        $this->assertEquals('http://example.com/en', LocaleUrlFacade::to('/'));
    }

}
