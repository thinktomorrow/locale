<?php

namespace Thinktomorrow\Locale\Tests\Values;

use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\ApplicationLocale;
use Thinktomorrow\Locale\Values\Locale;

class ApplicationLocaleTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $this->assertInstanceOf(ApplicationLocale::class, ApplicationLocale::from('nl'));
    }

    public function test_it_accepts_locale_as_well()
    {
        $this->assertInstanceOf(ApplicationLocale::class, ApplicationLocale::from(Locale::from('nl')));
    }

    public function test_it_can_compare()
    {
        $this->assertTrue(ApplicationLocale::from('nl')->equals(ApplicationLocale::from('nl')));
        $this->assertFalse(ApplicationLocale::from('nl')->equals(ApplicationLocale::from('fr')));
    }

    public function test_it_prints_out_as_string()
    {
        $this->assertEquals('nl', ApplicationLocale::from('nl'));
    }

    public function test_it_converts_to_application_locale()
    {
        $this->refreshLocaleBindings([
            'convert_locales'    => true,
            'convert_locales_to' => [
                'locale-ten' => 'convert-ten',
            ],
        ]);

        $this->assertEquals('convert-ten', ApplicationLocale::from('locale-ten')->get());
    }

    public function test_it_does_not_convert_to_application_locale_if_not_set_to_do_so()
    {
        $this->refreshLocaleBindings([
            'convert_locales'    => false,
            'convert_locales_to' => [
                'locale-ten' => 'convert-ten',
            ],
        ]);

        $this->assertEquals('locale-ten', ApplicationLocale::from('locale-ten')->get());
    }

    public function test_automatic_conversion_removes_region_portion_of_locale()
    {
        $this->refreshLocaleBindings([
            'convert_locales'    => 'auto',
            'convert_locales_to' => [],
        ]);

        $this->assertEquals('locale', ApplicationLocale::from('locale-ten')->get());
        $this->assertEquals('locale', ApplicationLocale::from('locale_ten')->get());
        $this->assertEquals('locale', ApplicationLocale::from('locale')->get());
    }
}
