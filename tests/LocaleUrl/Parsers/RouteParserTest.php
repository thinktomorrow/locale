<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl\Parsers;

use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Tests\TestCase;

class RouteParserTest extends TestCase
{
    private $routeParser;

    public function setUp() : void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://example.com', ['canonicals' => [
            'locale-one' => 'http://forced.com',
        ]]);
        Route::get('first/{slug?}', ['as' => 'route.first', 'uses' => function () {
        }]);
        Route::get(trans('routes.trans.first'), ['as' => 'trans.first', 'uses' => function () {
        }]);

        $this->routeParser = app()->make(RouteParser::class);
    }

    /** @test */
    public function parser_injects_locale_segment_if_needed()
    {
        $this->assertEquals('http://example.com/first/cow', $this->routeParser->set('route.first', ['slug' => 'cow'])->localize('/', ['/' => 'locale-three'])->get());
    }

    /** @test */
    public function parser_translates_route_segments_if_provided_via_lang_file()
    {
        $this->assertEquals('http://example.com/segment-one/first/cow', $this->routeParser->set('trans.first', ['slug' => 'cow'])->localize('segment-one', ['segment-one' => 'locale-one'])->get());
    }

    /** @test */
    public function if_route_translation_is_missing_translation_is_set_in_url()
    {
        $this->assertEquals('http://example.com/segment-four/routes.trans.first?slug=cow', $this->routeParser->set('trans.first', ['slug' => 'cow'])->localize('segment-four', ['segment-four' => 'locale-four'])->get());
    }

    /** @test */
    public function parser_halts_execution_if_route_is_not_defined()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->routeParser->set('foo.unknown')->localize('segment-one', ['segment-one' => 'locale-one'])->get();
    }

    /** @test */
    public function parser_can_create_a_secure_route()
    {
        $this->assertEquals('https://example.com/first/blue', $this->routeParser->set('route.first', ['blue'], true)->get());
    }
}
