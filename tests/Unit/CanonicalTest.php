<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Values\Canonical;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\ScopeCollection;
use Thinktomorrow\Locale\Tests\TestCase;

class CanonicalTest extends TestCase
{
    /** @test */
    function if_canonical_is_not_given_it_returns_null_for_canonical()
    {
        $this->assertNull($this->createScopeCollection([])->findCanonical('nl'));
    }

    /** @test */
    function it_can_find_canonical_scope_for_locale()
    {
        $scopeCollection = $this->createScopeCollection(['nl' => 'http://example.nl']);

        $this->assertEquals((new Scope(['/' => 'nl']))->setCustomRoot(Root::fromString('http://example.nl')), $scopeCollection->findCanonical('nl'));
    }

    /** @test */
    function it_defaults_to_default_scope_if_matching_canonical_host_isnt_a_valid_locale_key()
    {
        $scopeCollection = $this->createScopeCollection(['nl' => 'supervet', 'en' => 'awesome']);

        $this->assertEquals((new Scope(['/' => 'en']))->setCustomRoot(Root::fromString('supervet')), $scopeCollection->findCanonical('nl'));
        $this->assertEquals((new Scope(['/' => 'en']))->setCustomRoot(Root::fromString('awesome')), $scopeCollection->findCanonical('en'));
    }

    /** @test */
    function it_matches_pattern_group_keys()
    {
        $scopeCollection = $this->createScopeCollection(['fr' => 'http://fr.foobar.com']);

        $this->assertEquals((new Scope(['dk' => 'DK_dk', 'fr' => 'FR-fr', '/' => 'en']))->setCustomRoot(Root::fromString('http://fr.foobar.com')), $scopeCollection->findCanonical('fr'));
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