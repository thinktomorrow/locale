<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Services\Config;
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
    function it_can_get_all_config_values_but_sanitized()
    {
        $config = Config::from(['locales' => ['default' => 'nl']]);

        $this->assertEquals(['locales' => ['default' => ['/' => 'nl']]], $config->all());
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
            [['default' => ['en','fr']]], // missing hidden /
        ];
    }

    /**
     * @test
     * @dataProvider expectedStructureDataProvider
     */
    function it_converts_locales_to_expected_structure($original, $outcome)
    {
        $this->assertEquals($outcome, Config::from(['locales' => $original])->get('locales'));
    }

    function expectedStructureDataProvider()
    {
        return [
            [
                [
                    'default' => 'nl',
                ],
                [
                    'default' => ['/' => 'nl'],
                ],
            ],
            [
                [
                    'example.com' => ['/en' => 'en-gb'],
                    'default'     => 'nl',
                ],
                [
                    'example.com' => ['en' => 'en-gb'],
                    'default'     => ['/' => 'nl'],
                ],
            ],
            [
                [
                    '*.fr'    => 'fr',
                    'default' => 'nl',
                ],
                [
                    '*.fr'    => ['/' => 'fr'],
                    'default' => ['/' => 'nl'],
                ],
            ],
        ];
    }

}