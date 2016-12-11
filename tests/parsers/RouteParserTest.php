<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Support\Facades\Route;
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
    public function to_make_route_secure()
    {
        Route::get('{color}/foo/bar',['as' => 'foo.custom','uses' => function(){}]);

        $this->assertEquals('https://example.com/blue/foo/bar', $this->parser->set('foo.custom')->parameters(['color' => 'blue'])->secure()->get());
    }

}
