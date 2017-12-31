<?php

namespace Thinktomorrow\Locale\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleCanonicalTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshBindings();
    }

    /** @test */
    function it_can_find_the_canonical_for_current_locale()
    {
        Route::get('/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        // No explicit canonical set for en-gb so keep current root
        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE_Fr has explicit canonical
        app()->setLocale('FR_fr');
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE-Nl has explicit canonical but is not default locale of this root so it still needs a locale segment
        app()->setLocale('BE-nl');
        $this->assertEquals('https://www.foobar.com/nl/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // be-de has explicit canonical but is not default locale of this root so it still needs a locale segment
        app()->setLocale('BE-de');
        $this->assertEquals('https://german-foobar.de/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));
    }

    /** @test */
    function it_can_find_canonicals_for_specific_locale()
    {
        Route::get('/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('https://www.foobar.com/nl/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','BE-nl'));
    }

    /** @test */
    function scope_is_properly_reset_after_each_url_creation()
    {
        Route::get('/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
    }
}