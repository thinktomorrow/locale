<?php

namespace Thinktomorrow\Locale\Tests\Unit\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Values\Locale;

class LocaleTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Locale::class, Locale::from('nl'));
    }

    /** @test */
    function it_can_compare()
    {
        $this->assertTrue(Locale::from('nl')->equals(Locale::from('nl')));
        $this->assertFalse(Locale::from('nl')->equals(Locale::from('fr')));
    }

    /** @test */
    function it_prints_out_as_string()
    {
        $this->assertEquals('nl',Locale::from('nl'));
    }
}