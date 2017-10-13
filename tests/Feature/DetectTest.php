<?php

namespace Thinktomorrow\Locale\Tests\Feature;

use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Root;
use Thinktomorrow\Locale\Services\Scope;
use Thinktomorrow\Locale\Tests\TestCase;

class DetectTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Detect::class, $this->app->make(Detect::class));
    }

    /** @test */
    function by_default_it_will_detect_the_default_locale()
    {
        $detect = $this->detect(null, 'en');

        $this->assertEquals('foobar',$detect->getLocale());
        $this->assertEquals('foobar',app()->getLocale());
    }

    /** @test */
    function for_unknown_domains_it_uses_the_default_group()
    {
        // Default locale (hidden)
        $this->detect('http://sub.foobar.com/foo','dk');
        $this->assertEquals('foobar',app()->getLocale());

        // Segment locale
        $this->detect('http://foobar.com/en/foo');
        $this->assertEquals('en-GB',app()->getLocale());
    }

    /** @test */
    function for_known_domain_it_checks_specific_segment()
    {
        // Default locale (hidden)
        $this->detect('http://lu.example.com/fake');
        $this->assertEquals('lu',app()->getLocale());

        $this->detect('http://lu.example.com/en');
        $this->assertEquals('en',app()->getLocale());
    }

    /** @test */
    function for_known_domain_it_allows_the_default_locales_as_well()
    {
        // Default locale (hidden)
        $this->detect('http://lu.example.com/us');
        $this->assertEquals('en-US',app()->getLocale());
    }

    /** @test */
    function it_can_detect_wildcard_scope()
    {
        $this->detect('http://fr.crazy.com/');
        $this->assertEquals('fr',app()->getLocale());
    }

    /** @test */
    function unknown_query_parameter_defaults_to_default()
    {
        $this->detect('http://foobar.com?locale=xxx');
        $this->assertEquals('foobar',app()->getLocale());
    }

    /** @test */
    function passing_locale_can_be_passed_as_query_parameter()
    {
        $this->detect('http://foobar.com?locale=en-US');
        $this->assertEquals('en-US',app()->getLocale());
    }

    /** @test */
    function passing_locale_as_query_parameter_overrides_segments_logic()
    {
        $this->detect('http://foobar.com/en?locale=foobar');
        $this->assertEquals('foobar',app()->getLocale());
    }

    /** @test */
    function scope_can_be_overridden_at_runtime()
    {
        $this->detect('http://foobar.com/',null,new Scope(['aa' => 'bb','/' => 'cc'], Root::fromString('fake')));
        $this->assertEquals('cc',app()->getLocale());

        $this->detect('http://staging.example.be/aa',null,new Scope(['aa' => 'bb','/' => 'cc'], Root::fromString('fake')));
        $this->assertEquals('bb',app()->getLocale());
    }

    /** @test */
    function scope_can_be_overridden_with_new_binding()
    {
        $this->app->singleton(Detect::class, function ($app) {
            return (new Detect($app['request'], Config::from(['locales' => [
                'lu.example.com' => 'lu',
                '*' => 'foobar',
            ]])))->forceScope(new Scope(['aa' => 'bb','/' => 'cc'], Root::fromString('fake')));
        });

        $this->get('http://foobar.com/aa');
        app(Detect::class)->detect();

        $this->assertEquals('bb',app()->getLocale());
    }

    /** @test */
    function scope_can_be_overridden_more_than_once()
    {
        $this->app->singleton(Detect::class, function ($app) {
            return (new Detect($app['request'], Config::from(['locales' => [
                'lu.example.com' => 'lu',
                '*' => 'foobar',
            ]])))->forceScope(new Scope(['aa' => 'bb','/' => 'cc'], Root::fromString('fake')));
        });

        $this->get('http://foobar.com/aa');

        app(Detect::class)->detect();
        $this->assertEquals('bb',app()->getLocale());

        app(Detect::class)->forceScope(new Scope(['aa' => 'ee','/' => 'ff'], Root::fromString('fake')))->detect();
        $this->assertEquals('ee',app()->getLocale());
    }

    /** @test */
    function it_uses_the_current_request_root_as_scope_identifier()
    {
        $detect = $this->detect('http://staging.example.be/awesome/link');

        $this->assertEquals(Root::fromString('staging.example.be'), $detect->getScope()->root());
    }

    private function detect($uri = null, $originalLocale = null, $forceScope = null)
    {
        if($uri) $this->get($uri);
        if($originalLocale) $this->app->setLocale($originalLocale);

        return (new Detect($this->app->make('request'), Config::from([
            'query_key' => 'locale',
            'locales' => [
                'lu.example.com'   => [
                    '/en' => 'en',
                    '/'   => 'lu',
                ],
                'staging.example.be' => 'nl',
                'example.com'   => [
                    '/dl' => 'dk',
                ],
                'fr.*'   => [
                    '/' => 'fr',
                ],
                '*' => [
                    'en' => 'en-GB',
                    '/us' => 'en-US',
                    '/'  => 'foobar',
                ],
            ]
        ])))->forceScope($forceScope)->detect();
    }
}