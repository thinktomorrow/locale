<?php

namespace Thinktomorrow\Locale\Tests\Scopes;

use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Facades\ScopeFacade;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Root;

class ScopeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://unknown.com');
    }

    /** @test */
    public function it_can_get_default_locale_in_scope()
    {
        $scope = new Scope(['segment-ten' => 'locale-ten', '/' => 'locale-zero']);
        $this->assertEquals('locale-zero', $scope->defaultLocale());
    }

    /** @test */
    public function it_can_get_default_locale_in_scope_via_facade()
    {
        $this->detectLocaleAfterVisiting('http://unknown.com');
        $this->assertEquals('locale-zero', ScopeFacade::defaultLocale());
    }

    /** @test */
    public function a_default_locale_is_taken_from_last_locale_if_omitted()
    {
        $scope = new Scope(['segment-ten' => 'locale-ten']);
        $this->assertEquals('locale-ten', $scope->defaultLocale());

        $scope = new Scope(['segment-ten' => 'locale-ten', 'segment-eleven' => 'locale-eleven']);
        $this->assertEquals('locale-eleven', $scope->defaultLocale());
    }

    public function test_it_throws_error_when_no_default_locale_can_be_guessed()
    {
        $this->expectException(InvalidScope::class);

        new Scope([]);
    }

    /** @test */
    public function it_can_list_all_locales_in_scope()
    {
        $this->assertEquals([
            'segment-four' => 'locale-four',
            'segment-five' => 'locale-five',
            '/'            => 'locale-zero',
        ], ScopeFacade::locales());
    }

    /** @test */
    public function it_can_get_locale_by_key()
    {
        $this->assertEquals(Locale::from('locale-five'), ScopeFacade::findLocale('segment-five'));
        $this->assertEquals(Locale::from('locale-zero'), ScopeFacade::findLocale('/'));
        $this->assertNull(ScopeFacade::findLocale('unknown'));
    }

    /** @test */
    public function it_can_get_segment_key_by_locale()
    {
        $this->assertEquals('segment-five', ScopeFacade::segment('locale-five'));
        $this->assertEquals('/', ScopeFacade::segment('locale-zero'));
        $this->assertNull(ScopeFacade::segment('unknown'));
    }

    /** @test */
    public function it_can_set_custom_root()
    {
        $customRoot = ScopeFacade::setCustomRoot(Root::fromString('awesome.be'))->customRoot();

        $this->assertInstanceOf(Root::class, $customRoot);
        $this->assertEquals(Root::fromString('awesome.be'), $customRoot);
    }

    /** @test */
    public function it_can_validate_if_the_locale_is_allowed_in_current_scope()
    {
        $this->assertTrue(ScopeFacade::validateLocale('locale-zero'));
        $this->assertTrue(ScopeFacade::validateLocale('locale-four'));
        $this->assertFalse(ScopeFacade::validateLocale('locale-one'));

        $this->assertTrue(ScopeFacade::validateSegment('segment-four'));
        $this->assertFalse(ScopeFacade::validateSegment('segment-one'));

        // Switch scope
        $this->detectLocaleAfterVisiting('http://example.com');

        $this->assertFalse(ScopeFacade::validateLocale('locale-zero'));
        $this->assertTrue(ScopeFacade::validateLocale('locale-four'));
        $this->assertTrue(ScopeFacade::validateLocale('locale-one'));

        $this->assertTrue(ScopeFacade::validateSegment('segment-four'));
        $this->assertTrue(ScopeFacade::validateSegment('segment-one'));
    }
}
