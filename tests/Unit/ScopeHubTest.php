<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Scope;
use Thinktomorrow\Locale\Services\ScopeHub;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;

class ScopeHubTest extends TestCase
{
    /** @test */
    function it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        ScopeHub::fromArray([]);
    }

    /**
     * @test
     * @dataProvider invalidLocalesDataProvider
     */
    function it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        ScopeHub::fromArray(['locales' => $locales]);
    }

    function invalidLocalesDataProvider()
    {
        return [
            [['foobar']],
            [['nl' => '']],
            [['nl', 'fr']],
            [['foobar']],
        ];
    }

    /**
     * @test
     * @dataProvider provideScopes
     */
    function it_can_match_groups($host, $locales)
    {
        $this->assertEquals(new Scope($locales), ScopeHub::fromArray([
            'locales' => [
                'de.example.com' => 'de',
                'example.com'    => 'en',
                'fr.*'           => 'fr',
                'default'        => 'nl',
            ],
        ])->findByHost($host));
    }

    function provideScopes()
    {
        return [
            // Regular
            ['http://foo.example.com', ['/' => 'nl']],
            ['foo.example.com', ['/' => 'nl']],
            ['de.example.com', ['/' => 'de']],
            ['sfr.example.com', ['/' => 'nl']],
            ['http://de.example.com:8000', ['/' => 'de']],
            ['https://example.com', ['/' => 'en']],

            // Regex
            ['fr.foobar.com', ['/' => 'fr']],
            ['https://fr.foobar.com', ['/' => 'fr']],
            ['fr.foobar.com', ['/' => 'fr']],
        ];
    }

    /**
     * @test
     * @dataProvider expectedScopeDataProvider
     */
    function it_returns_locales_to_scoped_group($scope, $original, $locales)
    {
        $this->assertEquals(new Scope($locales), ScopeHub::fromArray(['locales' => $original])->findByHost($scope));
    }

    function expectedScopeDataProvider()
    {
        return [
            [
                'foobar.com',
                [
                    'default' => 'nl',
                ],
                [
                    '/' => 'nl',
                ],
            ],
            [
                'example.com',
                [
                    'example.com' => ['/en' => 'en-gb'],
                    'default'     => 'nl',
                ],
                [
                    'en' => 'en-gb',
                    '/'  => 'nl',
                ],
            ],
            [
                '*.fr', // TODO this should be regex matching
                [
                    '*.fr'    => 'fr',
                    'default' => 'nl',
                ],
                [
                    '/' => 'fr',
                ],
            ],
        ];
    }


}