<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Tests\TestCase;

class TranslatedRouteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://example.com');
    }

    /** @test */
    public function it_can_translate_routename_with_param()
    {
        Route::get(trans('routes.trans.first'), ['as' => 'trans.first', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/segment-two/second/crazy', $this->localeUrl->route('trans.first', 'locale-two', 'crazy'));
        $this->assertEquals('http://example.com/segment-one/first/crazy', $this->localeUrl->route('trans.first', 'locale-one', 'crazy'));
    }

    /** @test */
    public function it_can_translate_routename_with_optional_param()
    {
        Route::get(trans('routes.trans.optional'), ['as' => 'trans.optional', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/segment-two/second', $this->localeUrl->route('trans.optional', 'locale-two'));
    }

    /** @test */
    public function it_can_translate_routename_with_multiple_param()
    {
        // foo/{slug}/{subcat?}/{tag}/end
        Route::get(trans('routes.trans.multiple'), ['as' => 'trans.multiple', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/segment-two/second/{slug}/{tag}/end', $this->localeUrl->route('trans.multiple', 'locale-two'));
        $this->assertEquals('http://example.com/segment-two/second/this/great/story/end', $this->localeUrl->route('trans.multiple', 'locale-two', ['this', 'great', 'story']));
        $this->assertEquals('http://example.com/segment-two/second/{slug}/great/story/end', $this->localeUrl->route('trans.multiple', 'locale-two', ['subcat' =>'great', 'tag' => 'story']));
        $this->assertEquals('http://example.com/segment-two/second/{slug}/great/story/end', $this->localeUrl->route('trans.multiple', 'locale-two', ['tag' => 'story', 'subcat' =>'great']));
        $this->assertEquals('http://example.com/segment-two/second/{slug}/great/{tag}/end', $this->localeUrl->route('trans.multiple', 'locale-two', ['subcat' => 'great']));
    }
}
