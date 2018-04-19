<?php

namespace Thinktomorrow\Locale\Tests\DetectingLocale;

use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Tests\TestCase;
use Thinktomorrow\Locale\Values\Config;

class OverridingDetectTest extends TestCase
{
    /** @test */
    public function it_detects_default_locale_with_invalid_query_parameter()
    {
        $this->assertEquals('locale-zero', $this->detectLocaleAfterVisiting('http://unknown.com?locale=xxx'));
    }

    /** @test */
    public function it_detects_locale_with_valid_query_parameter()
    {
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com?locale=locale-four'));
    }

    /** @test */
    public function locale_as_query_parameter_trumps_segment_locale()
    {
        // Sanity check
        $this->assertEquals('locale-five', $this->detectLocaleAfterVisiting('http://unknown.com/segment-five'));

        // Here we do the real trumping
        $this->assertEquals('locale-four', $this->detectLocaleAfterVisiting('http://unknown.com/segment-five?locale=locale-four'));
    }

    /** @test */
    public function scope_can_be_overridden_at_runtime()
    {
        $this->detectLocaleAfterVisiting('http://unknown.com');
        $detect = app(Detect::class)->setScope(new Scope(['segment-ten' => 'locale-ten', '/' => 'locale-eleven']))->detectLocale();
        $this->assertEquals('locale-eleven', $detect->getLocale()->get());

        $this->detectLocaleAfterVisiting('http://unknown.com/segment-ten');
        $detect = app(Detect::class)->setScope(new Scope(['segment-ten' => 'locale-ten', '/' => 'locale-eleven']))->detectLocale();
        $this->assertEquals('locale-ten', $detect->getLocale()->get());
    }

    /** @test */
    public function scope_can_be_overridden_with_binding_it_to_a_custom_instance()
    {
        app()->singleton(Detect::class, function ($app) {
            $config = Config::from(['locales' => ['*' => 'locale-ten']]);

            return (new Detect($app['request'], $config))->setScope(new Scope(['segment-eleven' => 'locale-eleven', '/' => 'locale-twelve']));
        });

        $this->get('http://unknown.com/segment-eleven');

        $this->assertEquals('locale-eleven', app(Detect::class)->getLocale());
        $this->assertEquals('locale-eleven', app()->getLocale());
    }
}
