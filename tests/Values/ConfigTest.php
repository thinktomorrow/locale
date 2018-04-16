<?php

namespace Thinktomorrow\Locale\Tests\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;

class ConfigTest extends TestCase
{
    /** @test */
    function it_expects_locales_as_key()
    {
        $this->expectException(InvalidConfig::class);

        Config::from([]);
    }

    /** @test */
    function it_sanitizes_passed_values()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);

        $this->assertEquals(['locales' => ['*' => ['/' => 'nl']]], $config->all());
    }

    /** @test */
    function it_cleans_up_trailing_slash_of_domain_key()
    {
        $config = Config::from([
            'locales' => [
                'two.example.com/' => 'locale-two',
                'example.com/'      => 'locale-three',
                '*'                => 'locale-zero',
            ]
        ]);

        $this->assertSame(['locales' => [
            'two.example.com'  => ['/' => 'locale-two'],
            'example.com'      => ['/' => 'locale-three'],
            '*'                => ['/' => 'locale-zero'],
        ]], $config->all());
    }

    /**
     * @test
     * @dataProvider invalidLocalesDataProvider
     */
    function it_halts_invalid_locales_structure($locales)
    {
        $this->expectException(InvalidConfig::class);

        Config::from(['locales' => $locales]);
    }

    function invalidLocalesDataProvider()
    {
        return [
            [['foobar']],
            [['nl' => '']],
            [['nl', 'fr']],
            [['foobar']],
            [['*' => ['en', 'fr']]], // missing hidden /
        ];
    }

    /**
     * @test
     * @dataProvider expectedStructureDataProvider
     */
    function it_normalized_passed_locales($original, $outcome)
    {
        $this->assertEquals($outcome, Config::from(['locales' => $original])->get('locales'));
    }

    function expectedStructureDataProvider()
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

    /** @test */
    function it_can_export_to_array()
    {
        $config = Config::from(['locales' => ['*' => 'nl'], 'foobar' => 'nl']);

        $this->assertEquals(['locales' => ['*' => ['/' => 'nl']], 'foobar' => 'nl'], $config->toArray());
    }

    /** @test */
    function it_can_set_value_by_key()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);
        $config[2] = 'foobar';
        $this->assertEquals('foobar', $config[2]);
    }

    /** @test */
    function it_can_unset_a_value()
    {
        $this->expectException(\InvalidArgumentException::class, 'No config value found');

        $config = Config::from(['locales' => ['*' => 'nl'], 'foobar' => 'nl']);
        unset($config['locales']);

        $config->get('locales');
    }

    /** @test */
    function it_can_check_if_key_exists()
    {
        $config = Config::from(['locales' => ['*' => 'nl']]);
        $this->assertTrue(isset($config['locales']));
        $this->assertFalse(isset($config['foobar']));
    }

}