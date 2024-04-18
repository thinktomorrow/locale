<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\ScopeCollection;
use Thinktomorrow\Locale\Values\Config;

class ScopeCollectionTest extends TestCase
{
    /** @test */
    public function it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        ScopeCollection::fromArray([]);
    }

    /** @test */
    public function it_can_create_collection_from_config()
    {
        $this->assertInstanceOf(
            ScopeCollection::class,
            ScopeCollection::fromConfig(Config::from(['locales' => ['*' => 'locale-zero']]))
        );
    }

    /**
     * @test
     * @dataProvider invalidLocalesDataProvider
     */
    public function it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        ScopeCollection::fromArray(['locales' => $locales]);
    }

    public static function invalidLocalesDataProvider()
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
    public function it_matches_the_proper_domain_and_locales($root, $locales)
    {
        $this->assertEquals(new Scope($locales), ScopeCollection::fromArray([
            'locales' => [
                'segment-ten.example.com'      => 'locale-ten',
                'segment-ten.example.com:8000' => 'locale-eleven',
                'example.com'                  => 'locale-twelve',
                'segment-thirteen.*'           => 'locale-thirteen',
                '*'                            => 'locale-zero',
            ],
        ])->findByRoot($root));
    }

    public static function provideRootScopes()
    {
        return [
            // Full matches
            ['http://example.com', ['/' => 'locale-twelve']],
            ['segment-ten.example.com', ['/' => 'locale-ten']],
            ['segment-ten.example.com:8000', ['/' => 'locale-eleven']],

            // Pattern matching
            ['https://segment-thirteen.unknown.com', ['/' => 'locale-thirteen']],
            ['segment-thirteen.foobar.com', ['/' => 'locale-thirteen']],

            // No matches or default
            ['segment-unknown.example.com', ['/' => 'locale-zero']],
            ['unknown.com', ['/' => 'locale-zero']],
        ];
    }

    /** @test */
    public function it_can_match_root_with_or_without_ending_slash()
    {
        $this->assertEquals(new Scope(['/' => 'locale-one']), ScopeCollection::fromConfig(Config::from([
            'locales' => [
                'example.com/' => 'locale-one',
                '*'            => 'locale-zero',
            ],
        ]))->findByRoot('http://www.example.com'));
    }

    /** @test */
    public function if_no_current_root_is_found_default_scope_is_returned()
    {
        $this->assertEquals(new Scope(['/' => 'locale-zero']), ScopeCollection::fromArray([
            'locales' => [
                'example.com' => 'locale-twelve',
                '*'           => 'locale-zero',
            ],
        ])->findByRoot(''));
    }

    /** @test */
    public function if_locale_already_exists_in_default_group_only_the_one_from_own_scope_it_used()
    {
        $collection = ScopeCollection::fromArray([
            'locales' => [
                'segment-ten.foobar.com' => 'locale-ten',
                '*'                      => ['segment-eleven' => 'locale-ten', '/' => 'locale-zero'],
            ],
        ]);

        $this->assertEquals((new Scope(['/' => 'locale-ten'])), $collection->findByRoot('segment-ten.foobar.com'));
    }
}
