<?php

namespace Thinktomorrow\Locale\Tests\DetectingLocale;

use Thinktomorrow\Locale\Tests\TestCase;

class DetectLocaleTest extends TestCase
{
    /** @test */
    public function it_detects_default_locale_when_nothing_matches()
    {
        $this->assertEquals('locale-zero', $this->detectLocaleAfterVisiting('http://unknown.com/'));
        $this->assertEquals('locale-zero', $this->detectLocaleAfterVisiting('http://unknown.com/foobar'));
    }

    /** @test */
    public function it_detects_locale_by_segment()
    {
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com/segment-four'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com/segment-four/foobar'));
    }

    /** @test */
    public function it_detects_locale_by_domain()
    {
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('http://example.com/'));
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('http://example.com/foobar'));
    }

    /** @test */
    public function it_detects_locale_by_domain_and_segment()
    {
        $this->assertEquals('locale-two', $this->detectLocaleAfterVisiting('http://example.com/segment-two'));
        $this->assertEquals('locale-two', $this->detectLocaleAfterVisiting('http://example.com/segment-two/foobar'));
    }

    /** @test */
    public function it_can_detect_locale_by_subdomain_segment()
    {
        $config = [
            'locales' => [
                '*.example.com' => [
                    'segment-ten' => 'locale-ten',
                    '/'           => 'locale-eleven',
                ],
            ],
        ];

        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://foobar.example.com/', $config));
        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://fr.example.com/amazing/search', $config));
        $this->assertEquals('locale-ten', $this->detectLocaleAfterVisiting('https://fr.example.com/segment-ten/amazing/search', $config));
    }

    /** @test */
    public function it_detects_a_match_with_the_first_sorted_domain()
    {
        $config = [
            'locales' => [
                'ten.example.com' => 'locale-ten',
                '*.example.com'   => 'locale-eleven',
            ],
        ];

        $this->assertEquals('locale-eleven', $this->detectLocaleAfterVisiting('https://foobar.example.com/', $config));
        $this->assertEquals('locale-ten', $this->detectLocaleAfterVisiting('https://ten.example.com/', $config));
    }

    /** @test */
    public function it_can_detect_locale_by_port()
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
    public function by_default_the_protocol_is_not_relevant_for_locale_detection()
    {
        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('//example.com/'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('//example.com/segment-four'));

        $this->assertEquals('locale-three', $this->detectLocaleAfterVisiting('https://example.com'));
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('https://example.com/segment-four'));
    }
}
