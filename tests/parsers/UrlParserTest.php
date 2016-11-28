<?php

namespace Thinktomorrow\Locale\Tests;

use Thinktomorrow\Locale\Parsers\UrlParser;

class UrlParserTest extends TestCase
{
    private $parser;

    public function setUp()
    {
        parent::setUp();

        $this->parser = app()->make(UrlParser::class);
    }

    /** @test */
    public function assert_urls_are_kept_untouched()
    {
        $urls = [
            'http://example.be/fr/foo/bar',
            'http://example.be/fr/foo/bar',
            'http://example.be/fr',
            'http://example.com/fr/foo/bar',
            'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar',
            'https://example.com/fr/foo/bar#index',
            '//example.com/fr/foo/bar',
            '/fr/foo/bar',
            'fr/foo/bar',
        ];

        foreach ($urls as $url) {
            $this->assertEquals($url, $this->parser->set($url)->get(), 'improper conversion from ' . $url . ' to ' . $this->parser->set($url)->get() . ' - ' . $url . ' was expected.');
        }
    }

    /** @test */
    public function assert_urls_are_injected_with_locale_slug()
    {
        app()->setLocale('fr');

        $urls = [
            '/foo/bar' => '/fr/foo/bar',
            'foo/bar' => '/fr/foo/bar',
            '' => '/fr/',
            'http://example.com' => 'http://example.com/fr',
            'http://example.com/foo/bar' => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/foo/bar' => 'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar' => 'https://example.com/fr/foo/bar',
            'https://example.com/foo/bar#index' => 'https://example.com/fr/foo/bar#index',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, $this->parser->set($original)->localize('fr')->get(), 'improper conversion from ' . $original . ' to ' . $this->parser->set($original)->localize('fr')->get() . ' - ' . $result . ' was expected.');
        }
    }

    /** @test */
    public function an_invalid_url_is_not_accepted()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->parser->set('http:///example.com');
    }

    /** @test */
    public function assert_that_an_url_is_set()
    {
        $this->setExpectedException(\LogicException::class);

        $this->parser->localize('en');
    }

}
