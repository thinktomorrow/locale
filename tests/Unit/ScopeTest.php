<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\Services\Scope;

class ScopeTest extends TestCase
{
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
        $this->assertEquals(Locale::from('nl'), (new Scope(['nl' => 'nl', '/' => 'fr']))->get('nl'));
        $this->assertEquals(Locale::from('fr'), (new Scope(['nl' => 'nl', '/' => 'fr']))->get('/'));
    }

    /** @test */
    function not_found_key_returns_null()
    {
        $this->assertNull((new Scope(['nl' => 'nl', '/' => 'fr']))->get('foobar'));
    }

    /** @test */
    function it_can_get_all_locales_in_scope()
    {
        $locales = ['nl' => 'nl', '/' => 'fr'];
        $this->assertEquals($locales,(new Scope($locales))->all());
    }

    /** @test */
    function it_can_get_default_locale()
    {
        $this->assertEquals(Locale::from('fr'), (new Scope(['nl' => 'nl', '/' => 'fr']))->default());
    }

    /** @test */
    function validate_if_Locale_is_within_scope()
    {
        $scope = (new Scope(['nl' => 'nl', '/' => 'fr']));

        $this->assertFalse($scope->validate(Locale::from('en')));
        $this->assertTrue($scope->validate(Locale::from('nl')));
        $this->assertTrue($scope->validate(Locale::from('fr')));
    }
}