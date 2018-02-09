<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;

class LocalizedRoutesTest extends TestCase
{
    protected $localeUrl;

    public function setUp()
    {
        parent::setUp();

        $this->refreshBindings();
    }

    /** @test */
    public function to_translate_routename()
    {
        Route::get(trans('routes.foo.show'), ['as' => 'foo.show', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/bar/crazy', $this->localeUrl->route('foo.show', 'en', 'crazy'));
    }

    /** @test */
    public function to_translate_routename_with_optional_param()
    {
        Route::get(trans('routes.foo.index'), ['as' => 'foo.index', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->route('foo.index', 'en'));
    }

    /** @test */
    public function to_translate_routename_with_multiple_param()
    {
        // foo/{slug}/{subcat?}/{tag}/end
        Route::get(trans('routes.foo.multiple'), ['as' => 'foo.multiple', 'uses' => function () {
        }]);

        $this->assertEquals('http://example.com/en/foo/{slug}/{tag}/end', $this->localeUrl->route('foo.multiple', 'en'));
        $this->assertEquals('http://example.com/en/foo/this/great/story/end', $this->localeUrl->route('foo.multiple', 'en', ['this', 'great', 'story']));
        $this->assertEquals('http://example.com/en/foo/{slug}/great/story/end', $this->localeUrl->route('foo.multiple', 'en', ['subcat' =>'great', 'tag' => 'story']));
        $this->assertEquals('http://example.com/en/foo/{slug}/great/story/end', $this->localeUrl->route('foo.multiple', 'en', ['tag' => 'story', 'subcat' =>'great']));
        $this->assertEquals('http://example.com/en/foo/{slug}/great/{tag}/end', $this->localeUrl->route('foo.multiple', 'en', ['subcat' => 'great']));
    }
}
