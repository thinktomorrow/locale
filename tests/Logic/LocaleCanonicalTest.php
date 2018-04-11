<?php

namespace Thinktomorrow\Locale\Tests\Logic;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleCanonicalTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshLocaleBindings();

        Route::get('/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);
    }

    /** @test */
    function it_can_find_the_canonical_for_current_locale()
    {
        // No explicit canonical set for en-gb so keep current root
        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE_Fr has explicit canonical
        app()->setLocale('FR_fr');
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE-Nl has explicit canonical but is not default locale of this root so it still needs a locale segment
        app()->setLocale('BE-nl');
        $this->assertEquals('http://www.foobar.com/nl/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // DE_de has explicit canonical but is not default locale of this root so it still needs a locale segment
        app()->setLocale('DE_de');
        $this->assertEquals('https://german-foobar.de/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));
    }

    /** @test */
    function it_can_find_canonicals_for_specific_locale()
    {
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://www.foobar.com/nl/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','BE-nl'));
    }

    /** @test */
    function parsing_canonical_route_removes_locale_segment_coming_from_current_scope()
    {
        $this->get('http://example.com/de');
        Route::get('/fr/foo/bar', ['as' => 'foo.custom', 'uses' => function () {}]);

        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
    }


    /** @test */
    function it_can_get_the_canonical_for_current_locale()
    {
        // No explicit canonical set for en-gb so keep current root
        app()->setLocale('en-gb');
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE_Fr has explicit canonical
        app()->setLocale('FR_fr');
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));

        // BE-Nl has explicit canonical but is not default locale of this root so it still needs a locale segment
        app()->setLocale('BE-nl');
        $this->assertEquals('http://www.foobar.com/nl/foo/bar', $this->localeUrl->canonicalRoute('foo.custom'));
    }

    /** @test */
    function scope_is_properly_reset_after_each_url_creation()
    {
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
    }

    /** @test */
    function mixing_regular_route_and_canonicalized_should_return_expected_results()
    {
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
        $this->assertEquals('http://fr.foobar.com/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','FR_fr'));
        $this->assertEquals('http://example.com/en/foo/bar', $this->localeUrl->canonicalRoute('foo.custom','en-gb'));
    }

    /** @test */
    function if_canonical_is_not_set_it_is_taken_from_first_locale__domain()
    {
        $this->refreshLocaleBindings('nl',null, [
            'locales' => [
                'https://german-foobar.de' => 'de',
                'https://www.foobar.dk' => 'dk',
                '*.foobar.fr' => 'fr',
                '*' => [
                    'de' => 'de',
                    '/' => 'nl'
                ],
            ],
            'canonicals' => [],
        ]);

        $this->assertEquals('https://www.foobar.dk/foo/bar', localeroute('foo.custom','dk', [], true));
        $this->assertEquals('http://example.com/foo/bar', localeroute('foo.custom','de', [], true));
        $this->assertEquals('http://example.com/foo/bar', localeroute('foo.custom','nl', [], true));

        // Wildcard domains are not automatically picked as canonicals - we don't know the segment for fr in this case
        // so we revert to default
        $this->assertEquals('http://example.com/foo/bar', localeroute('foo.custom','fr', [], true));
    }
}