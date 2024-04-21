<?php

namespace Thinktomorrow\Locale\Tests\Scopes;

use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\ScopeRepository;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Url\Root;

class CanonicalScopeTest extends TestCase
{
    public function test_it_finds_the_expected_canonical_scope()
    {
        $this->detectLocaleAfterVisiting('http://example.com');

        $this->assertEquals(
            (new Scope(['/' => 'nl']))->setCustomRoot(Root::fromString('https://foobar.nl')),
            ScopeRepository::fromArray($this->canonicalConfig())->findCanonical('nl')
        );
    }

    public function it_find_canonical_scope_by_pattern()
    {
        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('uk.foobar.com')->secure()),
            ScopeRepository::fromArray($this->canonicalConfig())->findCanonical('en-gb')
        );

        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('us.foobar.com')->secure()),
            ScopeRepository::fromArray($this->canonicalConfig())->findCanonical('en-us')
        );
    }

    public function test_if_canonical_locale_does_not_exist_in_config_it_returns_null()
    {
        $this->assertNull(
            ScopeRepository::fromArray($this->canonicalConfig())->findCanonical('de')
        );
    }

    public function test_it_defaults_to_default_scope_if_matching_canonical_host_isnt_a_valid_locale_key()
    {
        $config = $this->canonicalConfig(['nl' => 'supervet', 'en-gb' => 'awesome']);

        $this->assertEquals((new Scope(['/' => 'en-gb']))->setCustomRoot(Root::fromString('supervet')), ScopeRepository::fromArray($config)->findCanonical('nl'));
        $this->assertEquals((new Scope(['/' => 'en-gb']))->setCustomRoot(Root::fromString('awesome')), ScopeRepository::fromArray($config)->findCanonical('en-gb'));
    }

    private function canonicalConfig(array $canonicals = null)
    {
        if (!$canonicals) {
            $canonicals = [
                'nl'    => 'https://foobar.nl',
                'en-gb' => 'https://uk.foobar.com',
                'en-us' => 'https://us.foobar.com',
            ];
        }

        return [
            'locales' => [
                'https://foobar.nl' => 'nl',
                '*.foobar.com'      => [
                    'us' => 'en-us',
                    '/'  => 'en-gb',
                ],
                '*' => 'en-gb',
            ],
            'canonicals' => $canonicals,
        ];
    }
}
