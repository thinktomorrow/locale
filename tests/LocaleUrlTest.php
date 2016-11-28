<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\LocaleUrl;

class LocaleUrlTest extends TestCase
{
    protected $localeUrl;

    public function setUp()
    {
        parent::setUp();

        $this->refreshBindings();
    }

    /** @test */
    public function it_defaults_an_unknown_locale_to_current_locale()
    {
        app(Locale::class)->set('fr');

        $this->assertEquals('http://example.be/fr/foo/bar', $this->localeUrl->to('http://example.be/foo/bar'));
        $this->assertEquals('http://example.be/fr/foo/bar', $this->localeUrl->to('http://example.be/foo/bar', 'fake'));
    }

    /** @test */
    public function it_can_create_an_url_with_locale_segment()
    {
        $urls = [
            '/foo/bar' => 'http://example.be/fr/foo/bar',
            'foo/bar' => 'http://example.be/fr/foo/bar',
            '' => 'http://example.be/fr',
            'http://example.com' => 'http://example.com/fr',
            'http://example.com/foo/bar' => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/foo/bar' => 'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar' => 'https://example.com/fr/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/fr/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'fr'), 'improper conversion from ' . $original . ' to ' . $this->localeUrl->to($original, 'fr') . ' - ' . $result . ' was expected.');
        }
    }

    /** @test */
    public function it_can_create_an_url_with_hidden_locale_segment()
    {
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
            $this->assertEquals($result, $this->localeUrl->to($original, 'nl'), 'improper conversion from ' . $original . ' to ' . $this->localeUrl->to($original, 'nl') . ' - ' . $result . ' was expected.');
        }
    }

    /** @test */
    public function it_can_create_a_named_route_with_locale_segment()
    {
        Route::get('foo/bar/{slug?}',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/fr/foo/bar', $this->localeUrl->route('foo.show', 'fr'));
        $this->assertEquals('http://example.be/foo/bar', $this->localeUrl->route('foo.show', 'nl'));
    }

    /** @test */
    public function it_can_create_a_named_route_with_nonlocale_segment()
    {
        app()->setLocale('en');
        Route::get('foo/bar/{slug?}',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/en/foo/bar', $this->localeUrl->route('foo.show'));
        $this->assertEquals('http://example.be/en/foo/bar/cow', $this->localeUrl->route('foo.show', 'cow'));
    }

    /** @test */
    public function it_can_create_a_named_route_with_first_dynamic_segment()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('en');
        $this->assertEquals('http://example.be/en/blue/foo/bar', $this->localeUrl->route('foo.show',['color' => 'blue']));
        $this->assertEquals('http://example.be/fr/blue/foo/bar', $this->localeUrl->route('foo.show','fr',['color' => 'blue']));

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/blue/foo/bar', $this->localeUrl->route('foo.show',['color' => 'blue']));
        $this->assertEquals('http://example.be/blue/foo/bar', $this->localeUrl->route('foo.show','nl',['color' => 'blue']));
    }

    /** @test */
    public function on_duplicate_locale_the_last_one_is_used()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/fr/blue/foo/bar', $this->localeUrl->route('foo.show','en',['locale_slug' => 'fr','color' => 'blue']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        app()->setLocale('en');
        $this->assertEquals('http://example.be/en/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.show',['color' => 'blue','dazzle' => 'awesome','crazy' => 'vibe']));

        app()->setLocale('nl');
        $this->assertEquals('http://example.be/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.show',['color' => 'blue','dazzle' => 'awesome','crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_a_named_route_with_multiple_segments_for_hidden_locale()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.show','uses' => function(){}]);

        $this->assertEquals('http://example.be/blue/foo/bar?dazzle=awesome&crazy=vibe', $this->localeUrl->route('foo.show',['color' => 'blue','dazzle' => 'awesome','crazy' => 'vibe']));
    }

    /** @test */
    public function it_can_create_translated_route_with_prefixed_route()
    {
        $this->refreshBindings('en');

        Route::group(['prefix' => app(Locale::class)->set('en')], function ()
        {
            Route::get('/foo/bar/{slug}',['as' => 'foo.show','uses' => function(){}]);
        });

        $this->assertEquals('http://example.be/en/foo/bar/blue', $this->localeUrl->route('foo.show',['locale_slug' => null,'color' => 'blue']));
        $this->assertEquals('http://example.be/foo/bar/blue', $this->localeUrl->route('foo.show',['locale_slug' => 'nl','color' => 'blue']));
        $this->assertEquals('http://example.be/fr/foo/bar/blue', $this->localeUrl->route('foo.show',['locale_slug' => 'fr','color' => 'blue']));
        $this->assertEquals('http://example.be/en/foo/bar/blue', $this->localeUrl->route('foo.show',['locale_slug' => 'en','color' => 'blue']));
    }

    private function refreshBindings($defaultLocale = 'nl',$hiddenLocale = 'nl')
    {
        app()->singleton('Thinktomorrow\Locale\Locale', function ($app) use($hiddenLocale) {
            return new Locale($app['request'], [
                'available_locales' => ['nl', 'fr', 'en'],
                'fallback_locale' => null,
                'hidden_locale' => $hiddenLocale
            ]);
        });

        // Force root url for testing
        app(UrlGenerator::class)->forceRootUrl('http://example.be');

        app()->singleton('Thinktomorrow\Locale\LocaleUrl',function($app){
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Locale'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Illuminate\Contracts\Routing\UrlGenerator'],
                ['placeholder' => 'locale_slug']
            );
        });

        $this->localeUrl = app(LocaleUrl::class);
        app()->setLocale($defaultLocale);
    }



}
