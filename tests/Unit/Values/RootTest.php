<?php

namespace Thinktomorrow\Locale\Tests\Unit\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidUrl;
use Thinktomorrow\Locale\Values\Root;

class RootTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Root::class, Root::fromString('nl'));
    }

    /** @test */
    public function it_normalizes_domain_to_complete_root()
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

            // Edgecases - are there any?
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

        $this->assertEquals(Root::fromString('uk.foobar.com')->secure(), Root::fromString('https://uk.foobar.com'));
    }

    /** @test */
    function it_can_validate_root()
    {
        $this->assertFalse(Root::fromString('')->valid());
        $this->assertTrue(Root::fromString('foobar')->valid());
        $this->assertTrue(Root::fromString('https://example.com')->valid());
    }

    /** @test */
    function instance_can_be_printed_as_string()
    {
        $this->assertEquals('http://foobar.com',Root::fromString('foobar.com'));
    }
    
    /** @test */
    function it_throws_exception_if_url_cannot_be_parsed()
    {
        $this->expectException(InvalidUrl::class);

        Root::fromString('javascript://');
    }

    /** @test */
    function it_can_get_scheme()
    {
        $urls = [
            'example.com'         => null,
            '//example.com'       => null,
            'http://example.com'  => 'http',
            'https://example.com' => 'https',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->scheme());
        }
    }
}