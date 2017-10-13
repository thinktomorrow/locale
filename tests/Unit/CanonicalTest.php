<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Thinktomorrow\Locale\Services\Canonical;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Locale;
use Thinktomorrow\Locale\Services\Root;
use Thinktomorrow\Locale\Services\Scope;
use Thinktomorrow\Locale\Services\ScopeHub;
use Thinktomorrow\Locale\Tests\TestCase;

class CanonicalTest extends TestCase
{
    /** @test */
    function if_canonical_is_not_given_it_returns_null_for_canonical()
    {
        $this->assertNull($this->createHub([])->findByCanonical(Locale::from('nl')));
    }

    /** @test */
    function it_defaults_to_default_scope_if_matching_canonical_host_isnt_a_valid_locale_key()
    {
        $hub = $this->createHub(['nl' => 'awesome']);

        $this->assertEquals(new Scope(['/' => 'en'],Root::fromString('foobar')), $hub->findByCanonical(Locale::from('nl')));
    }

    /** @test */
    function it_can_find_canonical_scope_for_locale()
    {
        $hub = $this->createHub(['nl' => 'http://example.nl']);

        $this->assertEquals(new Scope(['/' => 'nl'],Root::fromString('http://example.nl')), $hub->findByCanonical(Locale::from('nl')));
    }

    private function createHub(array $canonicals = [])
    {
        return ScopeHub::fromConfig(Config::from([
            'locales' => ['example.nl' => 'nl','*' => 'en'],
            'canonicals' => $canonicals
        ]), Root::fromString('foobar'));
    }
}