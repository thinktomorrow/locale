<?php

namespace Thinktomorrow\Locale\Tests\ConverLocale;

use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Tests\TestCase;

class ConvertLocaleTest extends TestCase
{
    /** @test */
    public function it_can_convert_locale_to_application_one()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/', [
            'locales' => [
                'convert.example.com' => [
                    '/'           => 'locale-ten',
                ],
            ],
            'convert_locales' => true,
            'convert_locales_to' => [
                'locale-ten' => 'converted-ten'
            ],
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('converted-ten', app()->getLocale());
    }

    /** @test */
    function it_can_automatically_convert_locale_to_application_one()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/', [
            'locales' => [
                'convert.example.com' => [
                    '/'           => 'locale-ten',
                ],
            ],
            'convert_locales' => 'auto',
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('locale', app()->getLocale());
    }

    /** @test */
    public function explicit_conversion_has_priority_over_automatic()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/', [
            'locales' => [
                'convert.example.com' => [
                    '/'           => 'locale-ten',
                ],
            ],
            'convert_locales' => 'auto',
            'convert_locales_to' => [
                'locale-ten' => 'converted-ten'
            ],
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('converted-ten', app()->getLocale());
    }
}
