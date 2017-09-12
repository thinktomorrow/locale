<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidLocale;
use Thinktomorrow\Locale\Locale;

class LocaleTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Locale::class, Locale::from('nl'));
    }

    /** @test */
    function it_prints_out_as_string()
    {
        $this->assertEquals('nl',Locale::from('nl'));
    }
}