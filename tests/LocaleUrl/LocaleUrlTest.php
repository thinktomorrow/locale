<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl;

use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Facades\LocaleUrlFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class LocaleUrlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Fake visiting this url
        $this->get('http://example.com');
        $this->refreshLocaleBindings();
    }

    /** @test */
    public function when_localizing_an_url_it_keeps_all_uri_segments_intact()
    {
        $urls = [
            '/foo/bar'                          => 'http://example.com/fr/foo/bar',
            'foo/bar'                           => 'http://example.com/fr/foo/bar',
            ''                                  => 'http://example.com/fr',
            'http://example.com'                => 'http://example.com/fr',
            'http://example.com/foo/bar'        => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/foo/bar'         => 'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar'    => 'https://example.com/fr/foo/bar',
            'https://example.com/es/foo/bar'    => 'https://example.com/fr/es/foo/bar', // Unknown locale segment for current scope is left untouched
            'https://example.com/foo/bar#index' => 'https://example.com/fr/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'BE_fr'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'fr').' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function when_localizing_an_url_with_default_locale_it_keeps_all_uri_segments_intact()
    {
        $urls = [
            '/foo/bar'                          => 'http://example.com/foo/bar',
            'foo/bar'                           => 'http://example.com/foo/bar',
            ''                                  => 'http://example.com',
            'http://example.com'                => 'http://example.com',
            'http://example.com/foo/bar'        => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/foo/bar?s=q',
            'http://example.nl/foo/bar'         => 'http://example.nl/foo/bar',
            'https://example.com/nl/foo/bar'    => 'https://example.com/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->localeUrl->to($original, 'FR_fr'), 'improper conversion from '.$original.' to '.$this->localeUrl->to($original, 'nl').' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function if_locale_is_detected_url_localisation_uses_active_locale_by_default()
    {
        $this->get('http://example.com/en');
        $this->assertEquals('http://example.com', $this->localeUrl->to('/')); // no locale known yet

        app(Detect::class)->detectLocale();
        $this->assertEquals('http://example.com/en', $this->localeUrl->to('/')); // locale is set as 'en' based on request
    }

    /** @test */
    public function a_localeurl_facade_can_be_used_for_convenience()
    {
        $this->get('http://example.com/en');
        app(Detect::class)->detectLocale();

        $this->assertEquals('http://example.com/en', LocaleUrlFacade::to('/'));
    }

    /** @test */
    function if_secure_config_is_true_urls_are_created_as_secure()
    {
        $this->refreshLocaleBindings('nl',null,['secure' => true]);

        $this->assertEquals('https://example.com/en/foo/bar', localeurl('http://example.com/foo/bar', 'en-gb'));
    }

    /** @test */
    function parameter_has_priority_over_secure_config()
    {
        $this->get('http://example.com');
        $this->refreshLocaleBindings('nl',null,['secure' => true]);

        $this->assertEquals('http://example.com/en/foo/bar', localeurl('http://example.com/foo/bar', 'en-gb', [], false));
        $this->assertEquals('https://example.com/en/foo/bar', localeurl('http://example.com/foo/bar', 'en-gb', [], true));
    }
}
