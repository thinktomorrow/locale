<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl;

use Illuminate\Support\Facades\Route;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Locale;

class LocaleCanonicalTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('https://example.com', ['secure' => true]);
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);
    }

    protected function detectLocaleAfterVisiting($url, array $overrides = []): string
    {
        return parent::detectLocaleAfterVisiting($url, array_merge([
            'locales' => [
                '*.custom-domain.com' => 'locale-thirteen',
                'one-domain.com'      => 'locale-one',
                'custom-domain.com'   => 'locale-ten',
                'eleventh-domain.com'     => [
                    'segment-eleven' => 'locale-eleven',
                    'segment-twelve' => 'locale-twelve',
                ],
            ],
            'canonicals' => [
                'locale-one'    => 'overridden-domain.com',
                'locale-ten'    => 'https://custom-domain.com',
                'locale-eleven' => 'http://www.eleventh-domain.com',
            ],
        ], $overrides));
    }

    public function test_current_root_is_used_by_default()
    {
        Scope::setActiveLocale(Locale::from('locale-two'));

        $this->assertEquals('https://example.com/segment-two/first', $this->localeUrl->canonicalRoute('route.first'));
        $this->assertEquals('https://example.com/segment-two/first', localeroute('route.first', null, [], true));
    }

    public function test_a_locale_can_have_an_explicit_canonical()
    {
        // Custom canonical points to default routes if root cannot be matched against available scopes
        Scope::setActiveLocale(Locale::from('locale-one'));

        $this->assertEquals('https://overridden-domain.com/first', $this->localeUrl->canonicalRoute('route.first'));

        // Locale-two is outside the default scope so it is ignored as locale and set als url param (slug) instead
        // By default the route is secured if not explicitly set not to secure.
        $this->assertEquals('https://custom-domain.com/first', $this->localeUrl->canonicalRoute('route.first', 'locale-ten'));
    }

    public function test_a_canonical_can_refer_to_a_locale_segment()
    {
        $this->assertEquals('https://www.eleventh-domain.com/segment-eleven/first', $this->localeUrl->canonicalRoute('route.first', 'locale-eleven'));
    }

    public function test_parsing_canonical_route_removes_locale_segment_coming_from_current_scope()
    {
        $this->detectLocaleAfterVisiting('https://example.com/segment-one');
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        $this->assertEquals('https://custom-domain.com/first', $this->localeUrl->canonicalRoute('route.first', 'locale-ten'));
    }

    public function test_canonical_is_computed_based_on_first_appearance_of_locale()
    {
        $this->assertEquals('https://eleventh-domain.com/segment-twelve/first', $this->localeUrl->canonicalRoute('route.first', 'locale-twelve'));

        // Wildcard domains are not automatically picked as canonicals - we don't know the segment for it in this case so we revert to current root
        $this->assertEquals('https://example.com/first/locale-thirteen', $this->localeUrl->canonicalRoute('route.first', 'locale-thirteen'));
    }

    public function test_if_secure_config_is_true_only_canonicals_with_scheme_can_be_explicitly_different()
    {
        $this->detectLocaleAfterVisiting('https://example.com', ['secure' => true]);
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);

        // Canonical has explicit http scheme so it is honoured
        $this->assertEquals('https://custom-domain.com/first', $this->localeUrl->canonicalRoute('route.first', 'locale-ten'));

        // Locale pointing to custom canonical but without explicit locale segment
        $this->assertEquals('https://overridden-domain.com/first/locale-one', $this->localeUrl->canonicalRoute('route.first', 'locale-one'));

        $this->assertEquals('https://example.com/first', localeroute('route.first', null, null, true));
    }
}
