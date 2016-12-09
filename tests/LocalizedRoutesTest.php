<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\LocaleUrl;

class LocalizedRoutesTest extends TestCase
{
    protected $localeUrl;

    public function setUp()
    {
        parent::setUp();

//        $this->refreshBindings();
    }

    public function test_it_does_nothing()
    {
        //
    }

//    /** @test */
//    public function to_translate_routename()
//    {
//        Route::get(trans('routes.foo.show'),['as' => 'foo.show','uses' => function(){}]);
//
//        $this->assertEquals('http://example.be/en/foo/bar/crazy', $this->localeUrl->route('foo.show', 'en','crazy'));
//    }
//
//    /** @test */
//    public function to_translate_routename_with_optional_param()
//    {
//        Route::get(trans('routes.foo.index'),['as' => 'foo.index','uses' => function(){}]);
//
//        $this->assertEquals('http://example.be/en/foo/bar', $this->localeUrl->route('foo.index', 'en'));
//    }
//
//    /** @test */
//    public function to_translate_routename_with_multiple_param()
//    {
//        // foo/{slug}/{subcat?}/{tag}/end
//        Route::get(trans('routes.foo.multiple'),['as' => 'foo.multiple','uses' => function(){}]);
//
//        $this->assertEquals('http://example.be/en/foo/{slug}/{tag}/end', $this->localeUrl->route('foo.multiple', 'en'));
//        $this->assertEquals('http://example.be/en/foo/this/great/story/end', $this->localeUrl->route('foo.multiple', 'en',['this','great','story']));
//        $this->assertEquals('http://example.be/en/foo/{slug}/great/story/end', $this->localeUrl->route('foo.multiple', 'en',['subcat' =>'great','tag' => 'story']));
//        $this->assertEquals('http://example.be/en/foo/{slug}/great/story/end', $this->localeUrl->route('foo.multiple', 'en',['tag' => 'story','subcat' =>'great']));
////        $this->assertEquals('http://example.be/en/foo/{slug}/great/{tag}/end', $this->localeUrl->route('foo.multiple', 'en',['subcat' => 'great']));
//    }
//
//    /** @test */
//    public function to_translate_routename_2()
//    {
////        Route::get('foo/bar/{slug?}',['as' => 'foo.show','uses' => function(){}]);
////
////        $this->assertEquals('http://example.be/fr/foo/bar', $this->localeUrl->route('foo.show', 'fr'));
////        $this->assertEquals('http://example.be/foo/bar', $this->localeUrl->route('foo.show', 'nl'));
//    }
//
//
//    private function refreshBindings($defaultLocale = 'nl',$hiddenLocale = 'nl')
//    {
//        app()->singleton('Thinktomorrow\Locale\Locale', function ($app) use($hiddenLocale) {
//            return new Locale($app['request'], [
//                'available_locales' => ['nl', 'fr', 'en'],
//                'fallback_locale' => null,
//                'hidden_locale' => $hiddenLocale
//            ]);
//        });
//
//        // Force root url for testing
//        app(UrlGenerator::class)->forceRootUrl('http://example.be');
//
//        app()->singleton('Thinktomorrow\Locale\LocaleUrl',function($app){
//            return new LocaleUrl(
//                $app['Thinktomorrow\Locale\Locale'],
//                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
//                $app['Illuminate\Contracts\Routing\UrlGenerator'],
//                ['placeholder' => 'locale_slug']
//            );
//        });
//
//        $this->localeUrl = app(LocaleUrl::class);
//        app()->setLocale($defaultLocale);
//    }



}
