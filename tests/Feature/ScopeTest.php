<?php

namespace Thinktomorrow\Locale\Tests\Feature;

use Thinktomorrow\Locale\DetectLocaleAndScope;
use Thinktomorrow\Locale\Facades\ScopeFacade;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Root;

class ScopeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->refreshBindings('nl','http://example.com');
        $this->get('http://example.com/en');
    }

    /** @test */
    public function detect_scope_from_request()
    {
        // Detected scope
        $this->assertEquals('en-gb', app(DetectLocaleAndScope::class)->detectLocale()->getScope()->activeLocale());
        $this->assertEquals('en', app(DetectLocaleAndScope::class)->detectLocale()->getScope()->activeSegment());
    }

    /** @test */
    public function detect_scope_with_facade()
    {
        $this->assertEquals('en-gb', ScopeFacade::activeLocale());
        $this->assertEquals('en', ScopeFacade::activeSegment());
    }

    /** @test */
    public function you_can_list_all_locales_in_scope()
    {
        $this->assertEquals(['nl' => 'BE-nl', 'en' => 'en-gb', '/' => 'nl'], ScopeFacade::locales());
    }

    /** @test */
    public function you_can_get_default_locale_in_scope()
    {
        $this->assertEquals('nl', ScopeFacade::defaultLocale());
    }

    /** @test */
    public function you_can_set_custom_root()
    {
        $this->assertInstanceOf(Root::class, ScopeFacade::setCustomRoot(Root::fromString('awesome.be'))->customRoot());
        $this->assertEquals(Root::fromString('awesome.be'), ScopeFacade::setCustomRoot(Root::fromString('awesome.be'))->customRoot());
    }

    /** @test */
    public function you_can_validate_the_locale()
    {
        $this->assertTrue(ScopeFacade::validateLocale('en-gb'));
        $this->assertFalse(ScopeFacade::validateLocale('en'));
    }

    /** @test */
    public function you_can_validate_the_locale_segment()
    {
        $this->assertTrue(ScopeFacade::validateSegment('en'));
        $this->assertFalse(ScopeFacade::validateSegment('en-gb'));
    }
}
