<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Tests\TestCase;

class RouteParserTest extends TestCase
{
    private $routeParser;

    public function setUp()
    {
        parent::setUp();

        $this->get('http://example.com');
        $this->refreshLocaleBindings();

        $this->routeParser = app()->make(RouteParser::class);
    }

    /** @test */
    public function parser_injects_locale_segment_if_needed()
    {
        $this->assertEquals('http://example.com/foz/baz/cow', $this->routeParser->set('foo.show',['slug' => 'cow'])->localize('/',['/' => 'nl'])->get());
    }

    /** @test */
    public function parser_translates_route_segments_if_provided_via_lang_file()
    {
        $this->assertEquals('http://example.com/en/foo/bar/cow', $this->routeParser->set('foo.show',['slug' => 'cow'])->localize('en',['en' => 'en-gb'])->get());
    }

    /** @test */
    public function parser_takes_default_route_translation_if_translation_is_missing_for_given_locale()
    {
        // Uses fallback locale (nl) for translation of routekey
        $this->assertEquals('http://example.com/fr/foz/baz/cow', $this->routeParser->set('foo.show', ['slug' => 'cow'])->localize('fr',['fr' => 'fr'])->get());
    }

    /** @test */
    public function parser_halts_execution_if_route_is_not_defined()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->routeParser->set('foo.unknown')->localize('fr',['fr' => 'fr'])->get();
    }

    /** @test */
    public function parser_can_create_a_secure_route()
    {
        $this->assertEquals('https://example.com/foz/baz/blue', $this->routeParser->set('foo.show', ['blue'],true)->get());
    }

}
