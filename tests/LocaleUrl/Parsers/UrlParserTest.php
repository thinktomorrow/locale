<?php

namespace Thinktomorrow\Locale\Tests\LocaleUrl\Parsers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\Tests\TestCase;

class UrlParserTest extends TestCase
{
    private $parser;

    public function setUp(): void
    {
        parent::setUp();

        $this->detectLocaleAfterVisiting('http://example.com');
        $this->parser = new UrlParser(app(UrlGenerator::class));
    }

    public function test_url_path_is_by_default_prepended_with_current_root()
    {
        $this->parser = new UrlParser(app(UrlGenerator::class));

        $urls = [
            '/foo/bar' => 'http://example.com/foo/bar',
            ''         => 'http://example.com',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->parser->set($original)->get(), 'improper conversion from '.$original.' to '.$this->parser->set($original)->get().' - '.$result.' was expected.');
        }

        $this->assertEquals('http://example.com/segment-one', $this->parser->localize('segment-one', ['segment-one' => 'locale-one'])->get());
    }

    public function test_without_localization_parameter_urls_arent_altered()
    {
        $urls = [
            'http://example.com/segment-one',
            'http://example.com/segment-one/foo/bar',
            'http://example.com/segment-one/foo/bar?s=q',
            'https://example.com/segment-one/foo/bar',
            'https://example.com/segment-one/foo/bar#index',
            '//example.com/segment-one/foo/bar',
            'http://example.com/segment-one/foo/bar',

            'http://unknown.be/segment-one/foo/bar',
        ];

        foreach ($urls as $url) {
            $this->assertEquals($url, $this->parser->set($url)->get(), 'improper conversion from '.$url.' to '.$this->parser->set($url)->get().' - '.$url.' was expected.');
        }
    }

    public function test_with_localization_parameter_url_is_injected_with_localeslug()
    {
        $urls = [
            '/foo/bar'                                => 'http://example.com/segment-one/foo/bar',
            'foo/bar'                                 => 'http://example.com/segment-one/foo/bar',
            ''                                        => 'http://example.com/segment-one',
            'http://example.com'                      => 'http://example.com/segment-one',
            'http://example.com/foo/bar'              => 'http://example.com/segment-one/foo/bar',
            'http://example.com/foo/bar?s=q'          => 'http://example.com/segment-one/foo/bar?s=q',
            'https://example.com/segment-one/foo/bar' => 'https://example.com/segment-one/foo/bar',
            'https://example.com/foo/bar#index'       => 'https://example.com/segment-one/foo/bar#index',

            'http://unknown.be/foo/bar' => 'http://unknown.be/segment-one/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->parser->set($original)->localize('segment-one', ['segment-one' => 'locale-one'])->get(), 'improper conversion from '.$original.' to '.$this->parser->set($original)->localize('locale-one', ['segment-one' => 'locale-one'])->get().' - '.$result.' was expected.');
        }
    }

    public function test_it_does_not_fail_on_parsing_double_slashed()
    {
        $this->assertEquals('//foobar.com/segment-one', $this->parser->set('//foobar.com//')->localize('segment-one', ['segment-one' => 'locale-one'])->get());
        $this->assertEquals('http://example.com/segment-one', $this->parser->set('//')->localize('segment-one', ['segment-one' => 'locale-one'])->get());
    }

    public function test_to_make_url_secure()
    {
        $urls = [
            '/foo/bar'                               => 'https://example.com/segment-one/foo/bar',
            'foo/bar'                                => 'https://example.com/segment-one/foo/bar',
            ''                                       => 'https://example.com/segment-one',
            'http://example.com/segment-one/foo/bar' => 'https://example.com/segment-one/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $parsed = $this->parser->set($original)->localize('segment-one', ['segment-one' => 'locale-one'])->secure()->get();
            $this->assertEquals($result, $parsed, 'improper conversion from '.$original.' to '.$parsed.' - '.$result.' was expected.');
        }
    }

    public function test_an_invalid_url_is_not_accepted()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->parser->set('http:///example.com');
    }
}
