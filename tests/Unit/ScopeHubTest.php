<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Illuminate\Contracts\Routing\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Services\Root;
use Thinktomorrow\Locale\Services\Scope;
use Thinktomorrow\Locale\Services\ScopeHub;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;

class ScopeHubTest extends TestCase
{
    /** @test */
    function it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        ScopeHub::fromArray([], Root::fromString('foobar'));
    }

    /**
     * @test
     * @dataProvider invalidLocalesDataProvider
     */
    function it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        ScopeHub::fromArray(['locales' => $locales], Root::fromString('foobar'));
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
     * @dataProvider provideRootScopes
     */
    function it_matches_the_proper_root_and_locales($root, $expectedRoot, $locales)
    {
        $this->assertEquals(new Scope($locales, Root::fromString($expectedRoot)), ScopeHub::fromArray([
            'locales' => [
                'de.example.com'      => 'de',
                'de.example.com:8000' => 'foo',
                'example.com'         => 'en',
                'fr.*'                => 'fr',
                '*'                   => 'nl',
            ],
        ], Root::fromString('foobar'))->findByRoot($root));
    }

    function provideRootScopes()
    {
        return [
            // Full matches
            ['http://example.com', 'http://example.com', ['/' => 'en']],
            ['de.example.com', 'de.example.com', ['/' => 'de']],
            ['http://de.example.com:8000', 'http://de.example.com:8000', ['/' => 'foo']],

            // Pattern matching
            ['https://fr.foobar.com', 'https://fr.foobar.com', ['/' => 'fr']],
            ['fr.foobar.com', 'fr.foobar.com', ['/' => 'fr']],

            // No matches or default
            ['sfr.example.com', 'foobar', ['/' => 'nl']],
            ['foobar.com', 'foobar', ['/' => 'nl']],
        ];
    }

    /** @test */
    function if_no_current_root_is_found_root_is_set_as_null()
    {
        $this->assertEquals(new Scope(['/' => 'nl'], Root::fromString('')), ScopeHub::fromArray([
            'locales' => [
                'example.com' => ['/en' => 'en-gb'],
                '*'           => 'nl',
            ],
        ], Root::fromString(''))->findByRoot(''));
    }

    /**
     * @test
     * @dataProvider expectedScopeDataProvider
     */
    function it_returns_locales_to_scoped_group($root, $original, $locales)
    {
        $this->assertEquals(new Scope($locales, Root::fromString($root)),
            ScopeHub::fromArray(['locales' => $original], Root::fromString($root))->findByRoot($root));
    }

    function expectedScopeDataProvider()
    {
        return [
            [
                'foobar.com',
                [
                    '*' => 'nl',
                ],
                [
                    '/' => 'nl',
                ],
            ],
            [
                'example.com',
                [
                    'example.com' => ['/en' => 'en-gb'],
                    '*'           => 'nl',
                ],
                [
                    'en' => 'en-gb',
                    '/'  => 'nl',
                ],
            ],
            [
                '*.fr',
                [
                    '*.fr' => 'fr',
                    '*'    => 'nl',
                ],
                [
                    '/' => 'fr',
                ],
            ],
            [
                'nothing', // return the default because nothing matches
                [
                    'example.com' => ['/en' => 'en-gb'],
                    '*'           => 'nl',
                ],
                [
                    '/' => 'nl',
                ],
            ],
        ];
    }


}