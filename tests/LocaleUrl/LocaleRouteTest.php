<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleRouteTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://example.com');

        // Route with optional param
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        // Route with required param
        Route::get('{slug}/second', ['as' => 'route.second', 'uses' => function () {
        }]);
    }

    /** @test */
    public function it_halts_execution_if_required_route_parameter_is_missing()
    {
        $this->expectException(UrlGenerationException::class);

        $this->localeUrl->route('route.second', 'locale-three');
    }

    /** @test */
    public function it_can_localize_a_route_by_passing_the_locale()
    {
        $this->assertEquals('http://example.com/segment-one/first', $this->localeUrl->route('route.first', 'locale-one'));
        $this->assertEquals('http://example.com/segment-one/first', $this->localeUrl->route('route.first', 'segment-one'));
        $this->assertEquals('http://example.com/first', $this->localeUrl->route('route.first', 'locale-three'));

        $this->assertEquals('http://example.com/first', LocaleUrlFacade::route('route.first', 'locale-three'));
        $this->assertEquals('http://example.com/first', localeroute('route.first', 'locale-three'));

        $this->assertEquals('http://example.com/first', $this->localeUrl->route('route.first', '/'));
    }

    /** @test */
    public function it_passed_locale_is_invalid_route_is_translated_according_to_application_locale()
    {
        app()->setLocale('locale-one');
        $this->assertEquals('http://example.com/segment-one/first/blue?xxx', $this->localeUrl->route('route.first', 'xxx', ['slug' => 'blue']));
    }

    /** @test */
    public function an_uri_parameter_can_be_passed_as_third_parameter()
    {
        $this->assertEquals('http://example.com/segment-one/first/crazy', $this->localeUrl->route('route.first', 'segment-one', 'crazy'));
        $this->assertEquals('http://example.com/segment-one/first/blue', $this->localeUrl->route('route.first', 'segment-one', ['slug' => 'blue']));
    }

    /** @test */
    public function an_uri_parameter_can_be_passed_as_second_parameter()
    {
        $this->assertEquals('http://example.com/cow/second', $this->localeUrl->route('route.second', 'cow'));
        $this->assertEquals('http://example.com/segment-one/cow/second', $this->localeUrl->route('route.second', 'cow', ['locale_slug' => 'segment-one']));
    }

    /** @test */
    public function it_can_localize_a_route_by_passing_the_segment()
    {
        $this->assertEquals('http://example.com/segment-one/first', $this->localeUrl->route('route.first', 'segment-one'));
        $this->assertEquals('http://example.com/first', $this->localeUrl->route('route.first', '/'));

        $this->assertEquals('http://example.com/segment-one/first/crazy', $this->localeUrl->route('route.first', 'segment-one', 'crazy'));
        $this->assertEquals('http://example.com/segment-one/blue/second', $this->localeUrl->route('route.second', 'segment-one', ['slug' => 'blue']));
    }

    /** @test */
    public function it_can_localize_a_route_by_passing_the_segment_as_parameter()
    {
        $this->assertEquals('http://example.com/first/blue', $this->localeUrl->route('route.first', ['locale_slug' => null, 'slug' => 'blue']));
        $this->assertEquals('http://example.com/first/blue', $this->localeUrl->route('route.first', ['locale_slug' => '/', 'slug' => 'blue']));

        // Locale passed as third parameter
        $this->assertEquals('http://example.com/segment-one/first/crazy', $this->localeUrl->route('route.first', 'segment-one', 'crazy'));
        $this->assertEquals('http://example.com/segment-one/first/blue', $this->localeUrl->route('route.first', 'segment-one', ['slug' => 'blue']));
        $this->assertEquals('http://example.com/segment-one/first/blue', $this->localeUrl->route('route.first', ['locale_slug' => 'segment-one', 'slug' => 'blue']));

        // Passed locale segment invalid or not present in current scope
        $this->assertEquals('http://example.com/first/blue?Foobar', $this->localeUrl->route('route.first', 'Foobar', ['slug' => 'blue']));
        $this->assertEquals('http://example.com/first/blue?xxx', $this->localeUrl->route('route.first', ['locale_slug' => 'xxx', 'slug' => 'blue']));
    }

    /** @test */
    public function in_case_of_duplicate_locale_parameter_the_one_passed_as_parameter_is_used()
    {
        $this->assertEquals('http://example.com/segment-one/blue/second', $this->localeUrl->route('route.second', 'locale-three', ['locale_slug' => 'segment-one', 'slug' => 'blue']));
    }

    /** @test */
    public function it_localizes_route_based_on_current_application_locale()
    {
        app()->setLocale('locale-one');
        $this->assertEquals('http://example.com/segment-one/blue/second', $this->localeUrl->route('route.second', ['slug' => 'blue']));

        app()->setLocale('locale-three');
        $this->assertEquals('http://example.com/blue/second', $this->localeUrl->route('route.second', ['slug' => 'blue']));
        $this->assertEquals('http://example.com/blue/second', $this->localeUrl->route('route.second', 'locale-three', ['slug' => 'blue']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments()
    {
        $this->assertEquals('http://example.com/blue/second?dazzle=awesome&crazy=vibe', $this->localeUrl->route('route.second', ['slug' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
        $this->assertEquals('http://example.com/segment-one/blue/second?dazzle=awesome&crazy=vibe', $this->localeUrl->route('route.second', 'locale-one', ['slug' => 'blue', 'dazzle' => 'awesome', 'crazy' => 'vibe']));
    }

    /** @test */
    public function if_secure_config_is_true_all_routes_are_created_as_secure()
    {
        $this->detectLocaleAfterVisiting('http://example.com', ['secure' => true]);
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        $this->assertEquals('https://example.com/segment-one/first', $this->localeUrl->route('route.first', 'locale-one'));
    }

    /** @test */
    public function if_secure_config_is_false_all_routes_are_created_as_given()
    {
        $this->detectLocaleAfterVisiting('http://example.com', ['secure' => false]);
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/first', $this->localeUrl->route('route.first'));
    }

    /** @test */
    public function localeroute_is_available_as_a_global_function()
    {
        $this->assertEquals('http://example.com/first', localeroute('route.first'));
        $this->assertEquals('http://example.com/segment-one/first', localeroute('route.first', 'locale-one'));
    }
}
