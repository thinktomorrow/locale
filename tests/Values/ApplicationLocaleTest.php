<?php

namespace Thinktomorrow\ApplicationLocale\Tests\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Values\ApplicationLocale;

class ApplicationLocaleTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(ApplicationLocale::class, ApplicationLocale::from('nl'));
    }

    /** @test */
    public function it_can_compare()
    {
        $this->assertTrue(ApplicationLocale::from('nl')->equals(ApplicationLocale::from('nl')));
        $this->assertFalse(ApplicationLocale::from('nl')->equals(ApplicationLocale::from('fr')));
    }

    /** @test */
    function it_can_auto_convert_to_language_locale_without_region()
    {
        $this->assertEquals(ApplicationLocale::from('nl'), ApplicationLocale::from('nl-BE')->withoutRegion());
        $this->assertEquals(ApplicationLocale::from('nl'), ApplicationLocale::from('nl_BE')->withoutRegion());
        $this->assertEquals(ApplicationLocale::from('NL'), ApplicationLocale::from('NL-NL')->withoutRegion());

        $this->assertEquals(ApplicationLocale::from('nl'), ApplicationLocale::from('nl')->withoutRegion());
    }

    /** @test */
    public function it_prints_out_as_string()
    {
        $this->assertEquals('nl', ApplicationLocale::from('nl'));
    }
}
