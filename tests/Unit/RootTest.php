<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Services\Root;

class RootTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Root::class, Root::fromString('nl'));
    }

    /** @test */
    public function it_converts_urls_to_proper_root()
    {
        $urls = [
            'example.com'                => 'http://example.com',
            'example.com/'               => 'http://example.com',
            'example.com/foo/bar'        => 'http://example.com',
            'foobar'                     => 'http://foobar',
            'foo/bar'                    => 'http://foo',
            'http://example.com'         => 'http://example.com',
            'https://example.com'        => 'https://example.com',
            'http://example.com/foo/bar' => 'http://example.com',
            'localhost:5000'             => 'http://localhost:5000',
            '127.0.0.1'                  => 'http://127.0.0.1',

            // Schemeless
            '//example.com/foo/bar?s=q'  => '//example.com',

            // Edgecases
            '/'                          => 'http:///', // Is this expected behaviour?
            '//'                         => 'http:///',
            ''                           => 'http://',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->get());
        }
    }

    /** @test */
    function it_can_set_root_as_secure()
    {
        $urls = [
            'example.com'         => 'https://example.com',
            'http://example.com'  => 'https://example.com',
            'https://example.com' => 'https://example.com',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->secure()->get());
        }
    }

    /** @test */
    function it_can_validate_root()
    {
        $this->assertFalse(Root::fromString('')->valid());
        $this->assertTrue(Root::fromString('foobar')->valid());
        $this->assertTrue(Root::fromString('https://example.com')->valid());
    }
}