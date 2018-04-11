<?php

namespace Thinktomorrow\Locale\Tests\Logic;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleRouteTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshLocaleBindings();

        Route::get('foo/bar/{slug?}', ['as' => 'foo.custom', 'uses' => function () {}]);
        Route::get('{color}/foo/bar', ['as' => 'bar.custom', 'uses' => function () {}]);
    }

    /** @test */
    public function it_can_localize_a_route()
    {
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foo.custom', 'BE_fr'));
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foo.custom', 'fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foo.custom', 'FR_fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foo.custom', '/'));
    }

    /** @test */
    public function if_locale_segment_is_passed_instead_of_the_locale_it_can_still_be_localized()
    {
        $this->assertEquals('http://example.com/fr/foo/bar', $this->localeUrl->route('foo.custom', 'fr'));
        $this->assertEquals('http://example.com/foo/bar', $this->localeUrl->route('foo.custom', '/'));
    }

    /** @test */
    public function an_uri_parameter_can_be_passed_as_third_parameter()
    {
        $this->assertEquals('http://example.com/fr/foo/bar/crazy', $this->localeUrl->route('foo.custom', 'fr', 'crazy'));
        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('bar.custom', 'fr', ['color' => 'blue']));
    }

    /** @test */
    public function an_uri_parameter_can_be_passed_as_second_parameter()
    {
        $this->assertEquals('http://example.com/foo/bar/cow', $this->localeUrl->route('foo.custom', 'cow'));
        $this->assertEquals('http://example.com/en/foo/bar/cow', $this->localeUrl->route('foo.custom', 'cow', ['locale_slug' => 'en']));

        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
    }

    /** @test */
    public function when_passing_duplicate_locale_parameters_the_explicit_one_is_used()
    {
        $this->assertEquals('http://example.com/fr/blue/foo/bar', $this->localeUrl->route('bar.custom', 'en', ['locale_slug' => 'fr', 'color' => 'blue']));
    }

    /** @test */
    public function a_route_where_first_segment_is_dynamic_does_not_conflict_with_locale_segment()
    {
        app()->setLocale('BE-nl');
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/nl/blue/foo/bar', $this->localeUrl->route('bar.custom', 'nl', ['color' => 'blue']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('bar.custom', ['color' => 'blue']));
        $this->assertEquals('http://example.com/blue/foo/bar', $this->localeUrl->route('bar.custom', 'FR_fr', ['color' => 'blue']));
    }

    /** @test */
    public function passing_excess_parameters_are_passed_as_query()
    {
        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('bar.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));

        app()->setLocale('FR_fr');
        $this->assertEquals('http://example.com/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('bar.custom', ['color' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    function if_passed_locale_is_not_in_current_scope_the_route_will_not_get_localized()
    {
        // Passed locale not present in current scope
        $this->assertEquals('http://example.com/foo/bar/blue?dk', $this->localeUrl->route('foo.custom', ['locale_slug' => 'dk', 'color' => 'blue']));
    }

    /** @test */
    public function a_localeurl_facade_can_be_used_for_convenience()
    {
        app()->setLocale('en-gb');

        $this->assertEquals('http://example.com/en/foo/bar', LocaleUrlFacade::route('foo.custom'));
    }

    /** @test */
    public function by_default_route_helper_localizes_route_to_current_locale()
    {
        $this->get('http://foobar.com/fr');

        Route::group(['prefix' => app(Detect::class)->detectLocale()->getScope()->activeSegment()], function () {
            Route::get('/foo/bar/{color}', ['as' => 'foo.custom', 'uses' => function () {
            }]);
        });

        $this->assertEquals('http://foobar.com/fr/foo/bar/blue', route('foo.custom', ['color' => 'blue']));
        $this->assertEquals('http://foobar.com/fr/foo/bar/blue', $this->localeUrl->route('foo.custom', ['color' => 'blue']));
    }

    /** @test */
    function if_secure_config_is_true_all_routes_are_created_as_secure()
    {
        $this->get('http://example.com');
        $this->refreshLocaleBindings('nl',null,['secure' => true]);

        $this->assertEquals('https://example.com/en/foo/bar', localeroute('foo.custom', 'en-gb'));
    }

    /** @test */
    function if_secure_config_is_true_only_canonicals_with_scheme_can_be_explicitly_different()
    {
        $this->get('http://example.com');
        $this->refreshLocaleBindings('nl',null,['secure' => true]);

        // Canonical has explicit http scheme so it is honoured
        $this->assertEquals('http://www.foobar.com/nl/foo/bar', localeroute('foo.custom', 'BE-nl', null, true));

        // Canonical has no specific scheme given so it receives https
        $this->assertEquals('https://fr.foobar.com/foo/bar', localeroute('foo.custom', 'FR_fr', null, true));

        // Canonical has https scheme
        $this->assertEquals('https://german-foobar.de/foo/bar', localeroute('foo.custom', 'DE_de', null, true));
    }

    /** @test */
    function if_secure_config_is_false_all_routes_are_created_as_given()
    {
        $this->get('http://example.com');
        $this->refreshLocaleBindings('nl',null,['secure' => false]);

        // Canonical has no specific scheme given so it receives https
        $this->assertEquals('http://fr.foobar.com/foo/bar', localeroute('foo.custom', 'FR_fr', null, true));

        // Canonical has https scheme so this is honoured
        $this->assertEquals('https://german-foobar.de/foo/bar', localeroute('foo.custom', 'DE_de', null, true));
    }
}
