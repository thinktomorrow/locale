<?php

namespace Thinktomorrow\Locale\Tests\Scopes;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Scope;

class ScopeTest extends TestCase
{
    private $scope;

    public function setUp()
    {
        parent::setUp();

        $this->scope = new Scope(['foo' => 'nl', '/' => 'fr']);
    }

    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Scope::class, new Scope(['nl' => 'nl', '/' => 'fr']));
    }

    /** @test */
    function default_locale_is_required()
    {
        $this->expectException(InvalidScope::class);

        new Scope(['nl' => 'nl']);
    }

    /** @test */
    function it_can_get_locale_by_key()
    {
        $this->assertEquals(Locale::from('nl'), $this->scope->findLocale('foo'));
        $this->assertEquals(Locale::from('fr'), $this->scope->findLocale('/'));
    }

    /** @test */
    function it_can_get_segment_key_by_locale()
    {
        $this->assertEquals('foo', $this->scope->segment('nl'));
        $this->assertEquals('/', $this->scope->segment('fr'));
        $this->assertNull($this->scope->segment('mohowseg'));
    }

    /** @test */
    function not_found_key_returns_null()
    {
        $this->assertNull($this->scope->findLocale('foobar'));
    }

    /** @test */
    function it_can_get_all_locales_in_scope()
    {
        $locales = ['nl' => 'nl', '/' => 'fr'];
        $this->assertEquals($locales,(new Scope($locales))->locales());
    }

    /** @test */
    function it_can_get_default_locale()
    {
        $this->assertEquals(Locale::from('fr'), $this->scope->defaultLocale());
    }

    /** @test */
    function validate_if_locale_is_within_scope()
    {
        $this->assertFalse($this->scope->validateLocale(Locale::from('en')));
        $this->assertTrue($this->scope->validateLocale(Locale::from('nl')));
        $this->assertTrue($this->scope->validateLocale(Locale::from('fr')));
    }
}