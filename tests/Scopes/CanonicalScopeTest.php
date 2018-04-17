<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\ScopeCollection;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Root;

class CanonicalScopeTest extends TestCase
{
    /** @test */
    public function it_finds_the_expected_canonical_scope()
    {
        $this->detectLocaleAfterVisiting('http://example.com');

        $this->assertEquals(
            (new Scope(['/' => 'nl']))->setCustomRoot(Root::fromString('https://foobar.nl')),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('nl')
        );
    }

    public function it_find_canonical_scope_by_pattern()
    {
        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('uk.foobar.com')->secure()),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('en-gb')
        );

        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('us.foobar.com')->secure()),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('en-us')
        );
    }

    /** @test */
    public function if_canonical_locale_does_not_exist_in_config_it_returns_null()
    {
        $this->assertNull(
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('de')
        );
    }

    /** @test */
    public function it_defaults_to_default_scope_if_matching_canonical_host_isnt_a_valid_locale_key()
    {
        $config = $this->canonicalConfig(['nl' => 'supervet', 'en-gb' => 'awesome']);

        $this->assertEquals((new Scope(['/' => 'en-gb']))->setCustomRoot(Root::fromString('supervet')), ScopeCollection::fromArray($config)->findCanonical('nl'));
        $this->assertEquals((new Scope(['/' => 'en-gb']))->setCustomRoot(Root::fromString('awesome')), ScopeCollection::fromArray($config)->findCanonical('en-gb'));
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
            'locales'    => [
                'https://foobar.nl' => 'nl',
                '*.foobar.com'      => [
                    'us' => 'en-us',
                    '/'  => 'en-gb',
                ],
                '*'                 => 'en-gb',
            ],
            'canonicals' => $canonicals,
        ];
    }
}
