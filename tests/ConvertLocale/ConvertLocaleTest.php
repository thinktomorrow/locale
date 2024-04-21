<?php

namespace Thinktomorrow\Locale\Tests\ConvertLocale;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Facades\ScopeFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class ConvertLocaleTest extends TestCase
{
    public function test_it_can_convert_locale_to_application_one()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/segment-ten', [
            'locales' => [
                'convert.example.com' => [
                    'segment-ten' => 'locale-ten',
                    '/'           => 'locale-eleven',
                ],
            ],
            'convert_locales'    => true,
            'convert_locales_to' => [
                'locale-ten' => 'converted-ten',
            ],
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('converted-ten', app()->getLocale());
        $this->assertEquals('locale-ten', ScopeFacade::activeLocale());
        $this->assertEquals('segment-ten', ScopeFacade::activeSegment());
    }

    public function test_route_is_translated_by_application_locale()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/segment-ten', [
            'locales' => [
                'convert.example.com' => [
                    'segment-ten' => 'locale-ten',
                    '/'           => 'locale-eleven',
                ],
            ],
            'convert_locales'    => true,
            'convert_locales_to' => [
                'locale-ten' => 'locale-twenty',
            ],
//            'secure' => false,
        ]);

        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('locale-twenty', app()->getLocale());
        $this->assertEquals('locale-ten', ScopeFacade::activeLocale());
        $this->assertEquals('segment-ten', ScopeFacade::activeSegment());

        $this->assertEquals('http://convert.example.com/segment-ten/first', localeroute('route.first', 'locale-ten'));

        // Application locale is not allowed and not encouraged
        $this->assertEquals('http://convert.example.com/segment-ten/first/locale-twenty', localeroute('route.first', 'locale-twenty'));
    }

    public function test_it_can_automatically_convert_locale_to_application_one()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/', [
            'locales' => [
                'convert.example.com' => [
                    '/' => 'locale-ten',
                ],
            ],
            'convert_locales' => 'auto',
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('locale', app()->getLocale());
    }

    public function test_explicit_conversion_has_priority_over_automatic()
    {
        $this->detectLocaleAfterVisiting('http://convert.example.com/', [
            'locales' => [
                'convert.example.com' => [
                    '/' => 'locale-ten',
                ],
            ],
            'convert_locales'    => 'auto',
            'convert_locales_to' => [
                'locale-ten' => 'converted-ten',
            ],
        ]);

        $this->assertEquals('locale-ten', app(Detect::class)->getLocale()->get());
        $this->assertEquals('converted-ten', app()->getLocale());
    }
}
