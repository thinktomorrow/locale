<?php

namespace Thinktomorrow\Locale\Tests\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Values\Locale;

class LocaleTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Locale::class, Locale::from('nl'));
    }

    /** @test */
    public function it_can_compare()
    {
        $this->assertTrue(Locale::from('nl')->equals(Locale::from('nl')));
        $this->assertFalse(Locale::from('nl')->equals(Locale::from('fr')));
    }
    
    /** @test */
    function it_can_auto_convert_to_language_locale_without_region()
    {
        $this->assertEquals(Locale::from('nl'), Locale::from('nl-BE')->withoutRegion());
        $this->assertEquals(Locale::from('nl'), Locale::from('nl_BE')->withoutRegion());
        $this->assertEquals(Locale::from('NL'), Locale::from('NL-NL')->withoutRegion());

        $this->assertEquals(Locale::from('nl'), Locale::from('nl')->withoutRegion());
    }

    /** @test */
    public function it_prints_out_as_string()
    {
        $this->assertEquals('nl', Locale::from('nl'));
    }
}
