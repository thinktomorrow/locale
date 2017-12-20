<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\Scopes\Scope;

class ScopeTest extends TestCase
{
    private $scope;

    public function setUp()
    {
        parent::setUp();

        $this->scope = new Scope(['foo' => 'nl', '/' => 'fr'], Root::fromString('foobar'));
    }

    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Scope::class, new Scope(['nl' => 'nl', '/' => 'fr'], Root::fromString('foobar')));
    }

    /** @test */
    function default_locale_is_required()
    {
        $this->expectException(InvalidScope::class);

        new Scope(['nl' => 'nl'], Root::fromString('foobar'));
    }

    /** @test */
    function it_can_get_locale_by_key()
    {
        $this->assertEquals(Locale::from('nl'), $this->scope->get('foo'));
        $this->assertEquals(Locale::from('fr'), $this->scope->get('/'));
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
        $this->assertNull($this->scope->get('foobar'));
    }

    /** @test */
    function it_can_get_all_locales_in_scope()
    {
        $locales = ['nl' => 'nl', '/' => 'fr'];
        $this->assertEquals($locales,(new Scope($locales, Root::fromString('foobar')))->all());
    }

    /** @test */
    function it_can_get_default_locale()
    {
        $this->assertEquals(Locale::from('fr'), $this->scope->default());
    }

    /** @test */
    function validate_if_locale_is_within_scope()
    {
        $this->assertFalse($this->scope->validate(Locale::from('en')));
        $this->assertTrue($this->scope->validate(Locale::from('nl')));
        $this->assertTrue($this->scope->validate(Locale::from('fr')));
    }
}