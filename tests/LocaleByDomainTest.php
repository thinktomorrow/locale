<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Detect;

class LocaleByDomainTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    function it_can_determine_locale_by_host()
    {
        $this->assertEquals('nl', $this->localeFor('http://example.com/'));
    }

    /** @test */
    function it_can_determine_locale_by_subdomain()
    {
        $this->assertEquals('fr', $this->localeFor('https://fr.example.com/amazing/search'));
    }

    /** @test */
    function protocol_does_not_matter()
    {
        $this->assertEquals('fr', $this->localeFor('http://fr.example.com/testje'));
    }

    /** @test */
    function for__domain_with_different_locales_it_gets_the_default_locale_if_no_locale_segment_is_present()
    {
        $this->assertEquals('dk', $this->localeFor('https://foobar.com/amazing/search'));
        $this->assertEquals('de', $this->localeFor('https://foobar.co.uk/amazing/search'));
    }

    /** @test */
    function it_can_determine_locale_by_domain_specific_segment()
    {
        $this->assertEquals('en-us', $this->localeFor('https://foobar.com/en/amazing/search'));
        $this->assertEquals('en-gb', $this->localeFor('https://foobar.co.uk/en/amazing/search'));
    }

    private function localeFor($url): string
    {
        $this->call('GET', $url);
        $locale = $this->createLocale();
        $locale->set();

        return $locale->get();
    }

    private function createLocale()
    {
        return new Detect(app()->make('request'), [
            'available_locales' => [
                'fr.example.com' => 'fr', // NOTE: put the specific ones on top
                'example.com' => 'nl',
                'foobar.com' => [
                    'en' => 'en-us',
                    '/' => 'dk',
                ],
                'foobar.co.uk' => [
                    'en' => 'en-gb',
                    '/' => 'de',
                ],
            ],
            'fallback_locale'   => 'en',
            'hidden_locale'     => null,
            'query_key'        => null,
        ]);
    }
}