<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Thinktomorrow\Locale\Scopes\CanonicalScope;
use Thinktomorrow\Locale\Values\Canonical;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\Scopes\Scope;
use Thinktomorrow\Locale\Scopes\ScopeCollection;
use Thinktomorrow\Locale\Tests\TestCase;

class CanonicalTest extends TestCase
{
    /** @test */
    function if_canonical_is_not_given_it_returns_null_for_canonical()
    {
        $this->assertNull($this->createScopeCollection([])->findCanonical('nl'));
    }

    /** @test */
    function it_defaults_to_default_scope_if_matching_canonical_host_isnt_a_valid_locale_key()
    {
        $hub = $this->createScopeCollection(['nl' => 'awesome']);

        $this->assertEquals(new CanonicalScope(['/' => 'en'],Root::fromString('foobar')), $hub->findCanonical('nl'));
    }

    /** @test */
    function it_can_find_canonical_scope_for_locale()
    {
        $hub = $this->createScopeCollection(['nl' => 'http://example.nl']);

        $this->assertEquals(new CanonicalScope(['/' => 'nl'],Root::fromString('http://example.nl')), $hub->findCanonical('nl'));
    }

    /** @test */
    function it_matches_pattern_group_keys()
    {
        $hub = $this->createScopeCollection(['fr' => 'http://fr.foobar.com']);

        $this->assertEquals(new CanonicalScope(['dk' => 'DK_dk', 'fr' => 'FR-fr', '/' => 'en'],Root::fromString('http://fr.foobar.com')), $hub->findCanonical('fr'));
    }

    private function createScopeCollection(array $canonicals = [])
    {
        return ScopeCollection::fromConfig(Config::from([
            'locales' => [
                'example.nl' => 'nl',
                '*.foobar.com' => [
                    'dk' => 'DK_dk',
                    'fr' => 'FR-fr',
                ],
                '*' => 'en'
            ],
            'canonicals' => $canonicals
        ]));
    }
}