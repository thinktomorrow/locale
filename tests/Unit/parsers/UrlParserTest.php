<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use Illuminate\Contracts\Routing\UrlGenerator;
use Thinktomorrow\Locale\Parsers\UrlParserContract;
use Thinktomorrow\Locale\Tests\TestCase;

class UrlParserTest extends TestCase
{
    private $parser;

    public function setUp()
    {
        parent::setUp();

        // Force root url for testing
        app(UrlGenerator::class)->forceRootUrl('http://example.com');
        $this->parser = new UrlParserContract(app(UrlGenerator::class));
    }

    /** @test */
    public function url_path_is_by_default_prepended_with_current_host()
    {
        $this->parser = new UrlParserContract(app(UrlGenerator::class));

        $urls = [
            '/foo/bar' => 'http://example.com/foo/bar',
            ''         => 'http://example.com',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->parser->set($original)->get(), 'improper conversion from '.$original.' to '.$this->parser->set($original)->get().' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function without_localization_parameter_urls_arent_altered()
    {
        $urls = [
            'http://example.com/fr',
            'http://example.com/fr/foo/bar',
            'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar',
            'https://example.com/fr/foo/bar#index',
            '//example.com/fr/foo/bar',
            'http://example.com/fr/foo/bar',
        ];

        foreach ($urls as $url) {
            $this->assertEquals($url, $this->parser->set($url)->get(), 'improper conversion from '.$url.' to '.$this->parser->set($url)->get().' - '.$url.' was expected.');
        }
    }

    /** @test */
    public function with_localization_parameter_url_is_injected_with_localeslug()
    {
        $urls = [
            '/foo/bar'                          => 'http://example.com/fr/foo/bar',
            'foo/bar'                           => 'http://example.com/fr/foo/bar',
            ''                                  => 'http://example.com/fr',
            'http://example.com'                => 'http://example.com/fr',
            'http://example.com/foo/bar'        => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q'    => 'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/foo/bar'         => 'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar'    => 'https://example.com/fr/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/fr/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->parser->set($original)->locale('fr',['fr' => 'fr'])->get(), 'improper conversion from '.$original.' to '.$this->parser->set($original)->locale('fr',['fr' => 'fr'])->get().' - '.$result.' was expected.');
        }
    }

    /**
    * @test
    */
    public function it_does_not_fail_on_parsing_double_slashed()
    {
        $this->assertEquals('//foobar.com/fr', $this->parser->set('//foobar.com//')->locale('fr',['fr' => 'BE-fr'])->get());
        $this->assertEquals('http://example.com/fr', $this->parser->set('//')->locale('fr',['fr' => 'BE-fr'])->get());
    }

    /** @test */
    public function to_make_url_secure()
    {
        $urls = [
            '/foo/bar'                      => 'https://example.com/fr/foo/bar',
            'foo/bar'                       => 'https://example.com/fr/foo/bar',
            ''                              => 'https://example.com/fr',
            'http://example.com/fr/foo/bar' => 'https://example.com/fr/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $parsed = $this->parser->set($original)->locale('fr',['fr' => 'BE-fr'])->secure()->get();
            $this->assertEquals($result, $parsed, 'improper conversion from '.$original.' to '.$parsed.' - '.$result.' was expected.');
        }
    }

    /** @test */
    public function an_invalid_url_is_not_accepted()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->parser->set('http:///example.com');
    }

    /** @test */
    public function give_default_root_if_an_url_is_not_set()
    {
        $this->assertEquals('http://example.com/en',$this->parser->locale('en',['en' => 'en'])->get());
    }
}
