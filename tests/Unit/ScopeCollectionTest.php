<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\ScopeCollection;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Root;

class ScopeCollectionTest extends TestCase
{
    /** @test */
    function it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        ScopeCollection::fromArray([]);
    }

    /**
     * @test
     */
    function it_can_create_collection_from_config()
    {
        $this->assertInstanceOf(ScopeCollection::class, ScopeCollection::fromConfig(Config::from(['locales' => ['*' => 'nl']])) );
    }

    /**
     * @test
     * @dataProvider invalidLocalesDataProvider
     */
    function it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        ScopeCollection::fromArray(['locales' => $locales]);
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
    function it_matches_the_proper_root_and_locales($root, $locales)
    {
        $this->assertEquals(new Scope($locales), ScopeCollection::fromArray([
            'locales' => [
                'de.example.com'      => 'de',
                'de.example.com:8000' => 'foo',
                'example.com'         => 'en',
                'fr.*'                => 'fr',
                '*'                   => 'nl',
            ],
        ])->findByRoot($root));
    }

    function provideRootScopes()
    {
        return [
            // Full matches
            ['http://example.com', ['/' => 'en']],
            ['de.example.com', ['/' => 'de']],
            ['http://de.example.com:8000', ['/' => 'foo']],

            // Pattern matching
            ['https://fr.foobar.com', ['/' => 'fr']],
            ['fr.foobar.com', ['/' => 'fr']],

            // No matches or default
            ['sfr.example.com', ['/' => 'nl']],
            ['foobar.com', ['/' => 'nl']],
        ];
    }

    /** @test */
    function if_no_current_root_is_found_root_is_set_as_default()
    {
        $this->assertEquals(new Scope(['/' => 'nl']), ScopeCollection::fromArray([
            'locales' => [
                'example.com' => ['/en' => 'en-gb'],
                '*'           => 'nl',
            ],
        ])->findByRoot(''));
    }

    /**
     * @test
     * @dataProvider expectedScopeDataProvider
     */
    function it_returns_locales_to_scoped_group($root, $original, $locales)
    {
        $this->assertEquals(new Scope($locales),
            ScopeCollection::fromArray(['locales' => $original])->findByRoot($root));
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

    /**
     * @test
     */
    function it_finds_the_expected_canonical_scope()
    {
        $this->assertEquals(
            (new Scope(['/' => 'nl']))->setCustomRoot(Root::fromString('https://foobar.nl')),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('nl')
        );

        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('uk.foobar.com')->secure()),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('en-gb')
        );

        $this->assertEquals(
            (new Scope(['us' => 'en-us', '/' => 'en-gb']))->setCustomRoot(Root::fromString('us.foobar.com')->secure()),
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('en-us')
        );
    }

    /**
     * @test
     */
    function if_canonical_locale_does_not_exist_in_config_it_returns_null()
    {
        $this->assertNull(
            ScopeCollection::fromArray($this->canonicalConfig())->findCanonical('de')
        );
    }

    private function canonicalConfig()
    {
        return [
            'locales' => [
                'https://foobar.nl' => 'nl',
                '*.foobar.com' => [
                    'us' => 'en-us',
                    '/' => 'en-gb',
                ],
                '*' => 'en-gb',
            ],
            'canonicals' => [
                'nl' => 'https://foobar.nl',
                'en-gb' => 'https://uk.foobar.com',
                'en-us' => 'https://us.foobar.com',
            ],
        ];
    }


}