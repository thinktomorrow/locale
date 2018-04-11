<?php

namespace Thinktomorrow\Locale\Tests\Logic\DetectingLocale;

use Thinktomorrow\Locale\Tests\TestCase;

class DetectLocaleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    function it_detects_default_locale_when_nothing_matches()
    {
        $this->assertEquals('locale-zero', $this->detectLocaleAfterVisiting('http://unknown.com/'));
        $this->assertEquals('locale-zero', $this->detectLocaleAfterVisiting('http://unknown.com/foobar'));
    }

    /** @test */
    function it_detects_locale_by_segment()
    {
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com/segment-four'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com/segment-four/foobar'));
    }

    /** @test */
    function it_detects_locale_by_domain()
    {
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('http://example.com/'));
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('http://example.com/foobar'));
    }

    /** @test */
    function it_detects_locale_by_domain_and_segment()
    {
        $this->assertEquals('locale-two', $this->detectLocaleAfterVisiting('http://example.com/segment-two'));
        $this->assertEquals('locale-two', $this->detectLocaleAfterVisiting('http://example.com/segment-two/foobar'));
    }

    /** @test */
    function it_can_detect_locale_by_subdomain_segment()
    {
        $config = [
            'locales' => [
                '*.example.com' => [
                    'segment-ten' => 'locale-ten',
                    '/' => 'locale-eleven',
                ],
            ],
        ];

        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://foobar.example.com/', $config));
        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://fr.example.com/amazing/search', $config));
        $this->assertEquals('locale-ten', $this->detectLocaleAfterVisiting('https://fr.example.com/segment-ten/amazing/search', $config));
    }

    /** @test */
    function it_can_detect_locale_by_port()
    {
        $config = [
            'locales' => [
                'localhost:4000' => 'locale-ten',
                'localhost:5000' => 'locale-eleven',
            ],
        ];

        $this->assertEquals('locale-ten', $this->detectLocaleAfterVisiting('https://localhost:4000', $config));
        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://localhost:5000', $config));
    }

    /** @test */
    function by_default_the_protocol_is_not_relevant_for_locale_detection()
    {
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('//example.com/'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('//example.com/segment-four'));

        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('https://example.com'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('https://example.com/segment-four'));
    }



//    private function createLocale($locales = null)
//    {
//        $locales = $locales ?? [
//            'fr.example.com' => 'fr', // NOTE: put the specific ones on top
//            'example.com' => 'nl',
//            'foobar.com' => [
//                'en' => 'en-us',
//                '/' => 'dk',
//            ],
//            'foobar.co.uk' => [
//                'en' => 'en-gb',
//                '/' => 'de',
//            ],
//            '*' => [
//                '/' => 'nl'
//            ],
//        ];
//
//        return new Detect(app()->make('request'),Config::from([
//            'locales' => $locales,
//            'fallback_locale'   => 'en',
//            'hidden_locale'     => null,
//            'query_key'        => null,
//        ]));
//    }
}