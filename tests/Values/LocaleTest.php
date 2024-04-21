<?php

namespace Thinktomorrow\Locale\Tests\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Values\Locale;

class LocaleTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $this->assertInstanceOf(Locale::class, Locale::from('nl'));
    }

    public function test_it_can_compare()
    {
        $this->assertTrue(Locale::from('nl')->equals(Locale::from('nl')));
        $this->assertFalse(Locale::from('nl')->equals(Locale::from('fr')));
    }

    public function test_it_can_auto_convert_to_language_locale_without_region()
    {
        $this->assertEquals(Locale::from('nl'), Locale::from('nl-BE')->withoutRegion());
        $this->assertEquals(Locale::from('nl'), Locale::from('nl_BE')->withoutRegion());
        $this->assertEquals(Locale::from('NL'), Locale::from('NL-NL')->withoutRegion());

        $this->assertEquals(Locale::from('nl'), Locale::from('nl')->withoutRegion());
    }

    public function test_it_can_extract_region_element_if_present()
    {
        $this->assertEquals('BE', Locale::from('nl-BE')->region());
        $this->assertEquals('BE', Locale::from('nl_BE')->region());
        $this->assertEquals('NL', Locale::from('NL-NL')->region());

        $this->assertNull(Locale::from('nl')->region());
    }

    public function test_it_prints_out_as_string()
    {
        $this->assertEquals('nl', Locale::from('nl'));
    }
}
