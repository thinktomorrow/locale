<?php

namespace Thinktomorrow\Locale\Tests\Feature;

use Thinktomorrow\Locale\Detect;
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
        $this->detect(null, 'en');

        $this->assertEquals('nl',app()->getLocale());
    }

    /** @test */
    function for_unknown_domains_it_uses_the_default_group()
    {
        // Default locale (hidden)
        $this->detect('http://sub.foobar.com/foo','dk');
        $this->assertEquals('nl',app()->getLocale());

        // Segment locale
        $this->detect('http://foobar.com/en/foo');
        $this->assertEquals('en-GB',app()->getLocale());
    }

    private function detect($uri = null, $originalLocale = null)
    {
        if($uri) $this->get($uri);
        if($originalLocale) $this->app->setLocale($originalLocale);

        return (new Detect($this->app->make('request'), [
            'fallback_locale' => 'de',
            'locales' => [
                'fr.example.com'   => [
                    '/en' => 'en',
                    '/'   => 'fr',
                ],
                'staging.example.be' => 'nl',
                'example.com'   => [
                    '/fr' => 'fr',
                ],
                'default' => [
                    'en' => 'en-GB',
                    '/us' => 'en-US',
                    'fr' => 'fr',
                    '/'  => 'nl',
                ],
            ]
        ]))();
    }
}