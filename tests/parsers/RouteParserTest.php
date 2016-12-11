<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Thinktomorrow\Locale\Parsers\RouteParser;

class RouteParserTest extends TestCase
{
    private $parser;

    public function setUp()
    {
        parent::setUp();

        $this->refreshBindings();

        $this->parser = app()->make(RouteParser::class);
    }

    /** @test */
    public function to_localize_route()
    {
        $this->assertEquals('http://example.com/foz/baz/cow', $this->parser->set('foo.show')->parameters(['slug' => 'cow'])->localize('nl')->get());
        $this->assertEquals('http://example.com/en/foo/bar/cow', $this->parser->set('foo.show')->parameters(['slug' => 'cow'])->localize('en')->get());
    }

    /** @test */
    public function to_localize_route_not_in_translations_takes_default_translation()
    {
        // Uses fallback locale for translation
        $this->assertEquals('http://example.com/fr/foz/baz/cow', $this->parser->set('foo.show')->parameters(['slug' => 'cow'])->localize('fr')->get());
    }

    /** @test */
    public function to_localize_route_not_in_any_translations()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->assertEquals('http://example.com/fr/foo.unknown', $this->parser->set('foo.unknown')->localize('fr')->get());
    }

    /** @test */
    public function to_make_route_secure()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.custom','uses' => function(){}]);

        $this->assertEquals('https://example.com/blue/foo/bar', $this->parser->set('foo.custom')->parameters(['color' => 'blue'])->secure()->get());
    }

}
