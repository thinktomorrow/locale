<?php

namespace Thinktomorrow\Locale\Tests\Scopes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\ScopeRepository;
use Thinktomorrow\Locale\Values\Config;

class ScopeRepositoryTest extends TestCase
{
    public function test_it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        ScopeRepository::fromArray([]);
    }

    public function test_it_can_create_collection_from_config()
    {
        $this->assertInstanceOf(
            ScopeRepository::class,
            ScopeRepository::fromConfig(Config::from(['locales' => ['*' => 'locale-zero']]))
        );
    }

    #[dataProvider('invalidLocalesDataProvider')]
    public function test_it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        ScopeRepository::fromArray(['locales' => $locales]);
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

    #[dataProvider('provideRootScopes')]
    public function test_it_matches_the_proper_domain_and_locales($root, $locales)
    {
        $this->assertEquals(new Scope($locales), ScopeRepository::fromArray([
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

    public function test_it_can_match_root_with_or_without_ending_slash()
    {
        $this->assertEquals(new Scope(['/' => 'locale-one']), ScopeRepository::fromConfig(Config::from([
            'locales' => [
                'example.com/' => 'locale-one',
                '*'            => 'locale-zero',
            ],
        ]))->findByRoot('http://www.example.com'));
    }

    public function test_if_no_current_root_is_found_default_scope_is_returned()
    {
        $this->assertEquals(new Scope(['/' => 'locale-zero']), ScopeRepository::fromArray([
            'locales' => [
                'example.com' => 'locale-twelve',
                '*'           => 'locale-zero',
            ],
        ])->findByRoot(''));
    }

    public function test_if_locale_already_exists_in_default_group_only_the_one_from_own_scope_it_used()
    {
        $collection = ScopeRepository::fromArray([
            'locales' => [
                'segment-ten.foobar.com' => 'locale-ten',
                '*'                      => ['segment-eleven' => 'locale-ten', '/' => 'locale-zero'],
            ],
        ]);

        $this->assertEquals((new Scope(['/' => 'locale-ten'])), $collection->findByRoot('segment-ten.foobar.com'));
    }
}
