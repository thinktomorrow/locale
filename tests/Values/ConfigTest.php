<?php

namespace Thinktomorrow\Locale\Tests\Values;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;
use Thinktomorrow\Locale\Values\Config;

class ConfigTest extends TestCase
{
    public function test_it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        Config::from([]);
    }

    public function test_it_sanitizes_passed_values()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);

        $this->assertEquals(['locales' => ['*' => ['/' => 'nl']], 'canonicals' => []], $config->all());
    }

    public function test_it_cleans_up_trailing_slash_of_domain_key()
    {
        $config = Config::from([
            'locales' => [
                'two.example.com/' => 'locale-two',
                'example.com/'     => 'locale-three',
                '*'                => 'locale-zero',
            ],
        ]);

        $this->assertSame([
            'two.example.com' => ['/' => 'locale-two'],
            'example.com'     => ['/' => 'locale-three'],
            '*'               => ['/' => 'locale-zero'],
        ], $config->get('locales'));
    }

    #[DataProvider('invalidLocalesDataProvider')]
    public function test_it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        Config::from(['locales' => $locales]);
    }

    public static function invalidLocalesDataProvider()
    {
        return [
            [['foobar']],
            [['nl' => '']],
            [['nl', 'fr']],
            [['foobar']],
            [['*' => ['en', 'fr']]], // missing segments
        ];
    }

    #[DataProvider('expectedStructureDataProvider')]
    public function test_it_normalized_passed_locales($original, $outcome)
    {
        $this->assertEquals($outcome, Config::from(['locales' => $original])->get('locales'));
    }

    public static function expectedStructureDataProvider()
    {
        return [
            [
                [
                    '*' => 'nl',
                ],
                [
                    '*' => ['/' => 'nl'],
                ],
            ],
            [
                [
                    'example.com' => ['/en' => 'en-gb'],
                    '*'           => 'nl',
                ],
                [
                    'example.com' => ['en' => 'en-gb'],
                    '*'           => ['/' => 'nl'],
                ],
            ],
            [
                [
                    '*.fr' => 'fr',
                    '*'    => 'nl',
                ],
                [
                    '*.fr' => ['/' => 'fr'],
                    '*'    => ['/' => 'nl'],
                ],
            ],
        ];
    }

    public function test_it_can_export_to_array()
    {
        $config = Config::from(['locales' => ['*' => 'nl'], 'foobar' => 'nl']);

        $this->assertEquals([
            'locales' => ['*' => ['/' => 'nl']], 'foobar' => 'nl', 'canonicals' => [],
        ], $config->toArray());
    }

    public function test_non_found_key_returns_default_value()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);

        $this->assertEquals('foobar', $config->get('unknown', 'foobar'));
        $this->assertEquals([], $config->get('unknown', []));
    }

    public function test_it_can_set_value_by_key()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);
        $config[2] = 'foobar';
        $this->assertEquals('foobar', $config[2]);
    }

    public function test_it_can_unset_a_value()
    {
        $config = Config::from(['locales' => ['*' => 'nl'], 'foobar' => 'nl']);
        unset($config['locales']);

        $this->assertNull($config->get('locales'));
    }

    public function test_it_can_check_if_key_exists()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);
        $this->assertTrue(isset($config['locales']));
        $this->assertFalse(isset($config['foobar']));
    }

    public function test_it_validates_that_each_explicit_canonical_exists_as_locale()
    {
        $this->expectException(InvalidConfig::class);

        Config::from([
            'locales' => [
                'example.com/' => 'locale-three',
                '*'            => 'locale-zero',
            ],
            'canonicals' => [
                'locale-unknown' => 'canonical.com',
            ],
        ]);
    }

    #[doesNotPerformAssertions]
    public function test_it_validates_canonical_exists_as_locale_when_using_multiple_domains()
    {
        // Test valid canonicals check with multiple domains
        Config::from([
            'locales' => [
                'convert.example.com' => [
                    'segment-ten' => 'locale-ten',
                    '/'           => 'locale-eleven',
                ],
                'example.com' => [
                    'segment-one' => 'locale-one',
                    'segment-two' => 'locale-two',
                    '/'           => 'locale-three',
                ],
                '*' => [
                    'segment-four' => 'locale-four',
                    'segment-five' => 'locale-five',
                    '/'            => 'locale-zero',
                ],
            ],
            'canonicals' => [
                'locale-ten'    => 'convert.example.com',
                'locale-eleven' => 'convert.example.com',
                'locale-one'    => 'example.com',
                'locale-two'    => 'example.com',
                'locale-three'  => 'example.com',
            ],
        ]);
    }

    public function test_it_computes_canonicals_for_all_locales_except_default()
    {
        $config = Config::from([
            'locales' => [
                'two.example.com/' => 'locale-two',
                'example.com/'     => 'locale-three',
                '*'                => 'locale-zero',
            ],
            'canonicals' => [
                'locale-three' => 'custom-canonical.com',
            ],
        ]);

        $this->assertSame([
            'locale-three' => 'custom-canonical.com',
            'locale-two'   => 'two.example.com',
        ], $config->get('canonicals'));
    }

    public function test_when_computing_canonicals_for_multiple_locales_it_takes_first_encountered_domain()
    {
        $config = Config::from([
            'locales' => [
                'two.example.com/' => [
                    'segment-three' => 'locale-three',
                    'segment-two'   => 'locale-two',
                    '/'             => 'locale-four',
                ],
                'example.com/' => 'locale-two',
                '*'            => 'locale-zero',
            ],
            'canonicals' => [
                'locale-three' => 'custom-canonical.com',
            ],
        ]);

        $this->assertSame([
            'locale-three' => 'custom-canonical.com',
            'locale-two'   => 'two.example.com',
            'locale-four'  => 'two.example.com',
        ], $config->get('canonicals'));
    }

    public function test_it_computes_canonicals_for_all_locales_except_wildcard_ones()
    {
        $config = Config::from([
            'locales' => [
                '*.example.com/'   => 'locale-two',
                'two.example.com/' => 'locale-two',
                'example.com/'     => 'locale-three',
                '*'                => 'locale-zero',
            ],
            'canonicals' => [
                'locale-three' => 'custom-canonical.com',
            ],
        ]);

        $this->assertSame([
            'locale-three' => 'custom-canonical.com',
            'locale-two'   => 'two.example.com',
        ], $config->get('canonicals'));
    }
}
