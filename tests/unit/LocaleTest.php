<?php

namespace Thinktomorrow\Locale\Tests;

use TestCase;
use Thinktomorrow\Locale\Locale;

class LocaleTest extends TestCase
{
    /** @test */
    public function it_can_be_called()
    {
        $locale = app()->make(Locale::class);

        $this->assertInstanceOf(Locale::class,$locale);
    }

    /** @test */
    public function it_gets_available_locale()
    {
        $locale = app()->make(Locale::class);

        $this->assertEquals('fr',$locale->getOrFail('fr'));
    }

    /** @test */
    public function it_gets_fallback_locale_if_locale_is_not_available()
    {
        $locale = app()->make(Locale::class);
        $locale->set();

        $this->assertEquals('nl',$locale->get('foo'));
    }

    /** @test */
    public function on_strict_mode_it_returns_false_if_locale_is_not_available()
    {
        $locale = app()->make(Locale::class);

        $this->assertFalse($locale->getOrFail('foo'));
        $this->assertFalse($locale->getOrFail('lu'));
    }

}
