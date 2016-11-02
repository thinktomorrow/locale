<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\LocaleUrl;

class LocaleUrlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app()->bind('Thinktomorrow\Locale\Locale', function ($app) {
            return new Locale($app['request'], [
                'available_locales' => ['nl', 'fr'],
                'fallback_locale' => null,
                'hidden_locale' => null
            ]);
        });

        // Force root url for testing
        app(UrlGenerator::class)->forceRootUrl('http://example.be');

        app()->bind('Thinktomorrow\Locale\LocaleUrl',function($app){
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Locale'],
                $app['Illuminate\Contracts\Routing\UrlGenerator'],
                ['placeholder' => 'locale_slug']
            );
        });
    }

    /** @test */
    public function it_defaults_an_unknown_locale_to_current_locale()
    {
        app(Locale::class)->set('fr');

        $this->assertEquals('http://example.be/fr/foo/bar', LocaleUrl::to('http://example.be/foo/bar'));
        $this->assertEquals('http://example.be/fr/foo/bar', LocaleUrl::to('http://example.be/foo/bar', 'fake'));
    }

    /** @test */
    public function it_can_create_an_url_with_locale_segment()
    {
        $urls = [
            '/foo/bar' => 'http://example.be/nl/foo/bar',
            'foo/bar' => 'http://example.be/nl/foo/bar',
            '' => 'http://example.be/nl',
            'http://example.com' => 'http://example.com/nl',
            'http://example.com/foo/bar' => 'http://example.com/nl/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/nl/foo/bar?s=q',
            'http://example.nl/foo/bar' => 'http://example.nl/nl/foo/bar',
            'https://example.com/nl/foo/bar' => 'https://example.com/nl/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/nl/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, LocaleUrl::to($original, 'nl'), 'improper conversion from ' . $original . ' to ' . LocaleUrl::to($original, 'nl') . ' - ' . $result . ' was expected.');
        }
    }

    /** @test */
    public function it_can_create_an_url_with_hidden_locale_segment()
    {
        app()->bind('Thinktomorrow\Locale\Locale', function ($app) {
            return new Locale($app['request'], [
                'available_locales' => ['nl', 'fr'],
                'fallback_locale' => null,
                'hidden_locale' => 'nl'
            ]);
        });

        $urls = [
            '/foo/bar' => 'http://example.be/foo/bar',
            'foo/bar' => 'http://example.be/foo/bar',
            '' => 'http://example.be',
            'http://example.com' => 'http://example.com/',
            'http://example.com/foo/bar' => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/foo/bar?s=q',
            'http://example.nl/foo/bar' => 'http://example.nl/foo/bar',
            'https://example.com/nl/foo/bar' => 'https://example.com/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, LocaleUrl::to($original, 'nl'), 'improper conversion from ' . $original . ' to ' . LocaleUrl::to($original, 'nl') . ' - ' . $result . ' was expected.');
        }
    }

    /** @test */
    public function it_can_create_a_named_route_with_locale_segment()
    {
        Route::get('foo/bar',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/nl/foo/bar', LocaleUrl::route('foo.show', 'nl'));
    }

    /** @test */
    public function it_can_create_a_named_route_from_default_locale()
    {
        Route::get('foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/nl/foo/bar', LocaleUrl::route('foo.show'));
    }

    /** @test */
    public function it_can_create_a_named_route_with_segment_on_left()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/nl/blue/foo/bar', LocaleUrl::route('foo.show',['color' => 'blue']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/nl/blue/foo/bar?dazzle=awesome&crazy=vibe', LocaleUrl::route('foo.show',['color' => 'blue','dazzle' => 'awesome','crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments_for_hidden_locale()
    {
        app()->bind('Thinktomorrow\Locale\Locale', function ($app) {
            return new Locale($app['request'], [
                'available_locales' => ['nl', 'fr'],
                'fallback_locale' => null,
                'hidden_locale' => 'nl'
            ]);
        });

        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/blue/foo/bar?dazzle=awesome&crazy=vibe', LocaleUrl::route('foo.show',['color' => 'blue','dazzle' => 'awesome','crazy' => 'vibe']));
    }



}
