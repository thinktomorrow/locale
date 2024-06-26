<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl;

use Thinktomorrow\Locale\Facades\LocaleUrlFacade;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Locale;

class LocaleUrlTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://example.com');
    }

    public function test_when_localizing_an_url_it_keeps_all_uri_segments_intact()
    {
        $urls = [
            '/foo/bar'                                 => 'http://example.com/segment-four/foo/bar',
            'foo/bar'                                  => 'http://example.com/segment-four/foo/bar',
            ''                                         => 'http://example.com/segment-four',
            'http://example.com'                       => 'http://example.com/segment-four',
            'http://example.com/foo/bar'               => 'http://example.com/segment-four/foo/bar',
            'http://example.com/foo/bar?s=q'           => 'http://example.com/segment-four/foo/bar?s=q',
            'https://example.com/segment-four/foo/bar' => 'https://example.com/segment-four/foo/bar',
            'http://example.com:4000/foo/bar'          => 'http://example.com:4000/segment-four/foo/bar',
            'https://example.com/foo/bar#index'        => 'https://example.com/segment-four/foo/bar#index',

            // removes existing valid locale segment
            'https://example.com/segment-five/foo/bar' => 'https://example.com/segment-four/foo/bar',

            // non-matching domain is passed as is
            'http://example.fr/foo/bar' => 'http://example.fr/segment-four/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'locale-four'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'fr').' - '.$result.' was expected.');
        }
    }

    public function test_when_localizing_an_url_with_default_locale_it_keeps_all_uri_segments_intact()
    {
        $urls = [
            '/foo/bar'                          => 'http://example.com/foo/bar',
            'foo/bar'                           => 'http://example.com/foo/bar',
            ''                                  => 'http://example.com',
            'http://example.com'                => 'http://example.com',
            'http://example.com/foo/bar'        => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/foo/bar?s=q',
            'https://example.com/foo/bar#index' => 'https://example.com/foo/bar#index',
            'http://example.com:4000/foo/bar'   => 'http://example.com:4000/foo/bar',

            // removes existing valid locale segment
            'https://example.com/segment-four/foo/bar' => 'https://example.com/foo/bar',

            // non-matching domain is passed as is
            'http://example.nl/foo/bar' => 'http://example.nl/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'locale-three'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'nl').' - '.$result.' was expected.');
        }
    }

    public function test_url_is_localized_with_current_detected_locale()
    {
        // default locale
        $this->assertEquals('http://example.com', $this->localeUrl->to('/'));
        $this->assertEquals('http://example.com', LocaleUrlFacade::to('/'));

        // specific locale is set
        $this->detectLocaleAfterVisiting('http://example.com/segment-four');
        $this->assertEquals('http://example.com/segment-four', $this->localeUrl->to('/'));
        $this->assertEquals('http://example.com/segment-four', LocaleUrlFacade::to('/'));
    }

    public function test_url_is_localized_with_passed_locale_only_if_locale_is_present_in_scope()
    {
        // passing specific locale within scope
        $this->assertEquals('http://example.com/segment-four', $this->localeUrl->to('/', 'locale-four'));
        $this->assertEquals('http://example.com/segment-four', LocaleUrlFacade::to('/', 'locale-four'));

        // passing locale outside of scope
        $this->detectLocaleAfterVisiting('http://unknown.com');
        $this->assertEquals('http://unknown.com', $this->localeUrl->to('/', 'locale-one'));
        $this->assertEquals('http://unknown.com', LocaleUrlFacade::to('/', 'locale-one'));
    }

    public function test_if_secure_config_is_true_urls_are_created_as_secure()
    {
        $this->refreshLocaleBindings(['secure' => true]);

        $this->assertEquals('https://example.com/segment-five/foo/bar', localeurl('http://example.com/foo/bar', 'locale-five'));
    }

    public function test_parameter_has_priority_over_secure_config()
    {
        $this->detectLocaleAfterVisiting('http://example.com', ['secure' => true]);

        $this->assertEquals('http://example.com/segment-five/foo/bar', localeurl('http://example.com/foo/bar', 'locale-five', [], false));
        $this->assertEquals('https://example.com/segment-five/foo/bar', localeurl('http://example.com/foo/bar', 'locale-five', [], true));
    }

    public function test_it_sanitizes_a_xss_injection()
    {
        $this->assertEquals(
            'http://example.com/segment-one/first/%22%2520qss%253D%22QssAttrValue',
            $this->localeUrl->to('first', 'locale-one', '"%20qss%3D"QssAttrValue')
        );
    }

    public function test_localeurl_is_available_as_a_global_function()
    {
        Scope::setActiveLocale(Locale::from('locale-two'));

        $this->assertEquals('http://example.com/segment-two', localeurl('/'));
        $this->assertEquals('http://example.com/segment-two/foobar', localeurl('/foobar'));
        $this->assertEquals('http://example.com/segment-one', localeurl('/', 'locale-one'));
    }
}
