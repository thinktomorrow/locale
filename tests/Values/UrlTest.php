<?php

namespace Thinktomorrow\Locale\Tests\Values;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Exceptions\InvalidUrl;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\Values\Url;

class UrlTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Url::class, Url::fromString('fake'));
    }

    /** @test */
    public function without_localization_parameter_urls_aren_not_altered()
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
            $this->assertEquals($url, Url::fromString($url)->get());
        }
    }

    /** @test */
    public function it_accepts_a_locale_segment()
    {
        $urls = [
            null                             => '/fr',
            '//'                             => '/fr',
            '/foo/bar'                       => '/fr/foo/bar',
            'foo/bar'                        => '/fr/foo/bar',
            ''                               => '/fr',
            'http://example.com'             => 'http://example.com/fr',
            'http://example.com/foo/bar'     => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/fr/foo/bar?s=q',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function it_can_set_a_hidden_locale()
    {
        $urls = [
            '/foo/bar'                       => '/foo/bar',
            'foo/bar'                        => '/foo/bar',
            ''                               => '',
            'http://example.com'             => 'http://example.com',
            'http://example.com/foo/bar'     => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/foo/bar?s=q',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize(null, ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function it_removes_existing_locale_segment()
    {
        $urls = [
            ''                              => '/nl',
            '/fr/foo/bar'                   => '/nl/foo/bar',
            'fr/foo/bar'                    => '/nl/foo/bar',
            'fr'                            => '/nl',
            'http://example.com/fr'         => 'http://example.com/nl',
            'http://example.com/fr/foo/bar' => 'http://example.com/nl/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize('nl', ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function it_keeps_passed_root_if_not_set_explicitly()
    {
        $this->assertEquals('http://example.fr/fr/foo/bar',
            Url::fromString('http://example.fr/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->get()
        );
    }

    /** @test */
    public function it_can_set_custom_root()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('/foo/bar')
                ->setCustomRoot(Root::fromString('https://example.com'))
                ->localize('fr')
                ->get()
        );
    }

    /** @test */
    public function it_can_set_url_as_secure()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('http://example.com/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->secure()
                ->get()
        );
    }

    /** @test */
    public function it_can_set_url_as_unsecure()
    {
        $this->assertEquals('http://example.com/fr/foo/bar',
            Url::fromString('https://example.com/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->secure(false)
                ->get()
        );
    }

    /** @test */
    public function it_can_check_if_given_url_is_absolute()
    {
        $urls = [
            '/foo/bar'                  => false,
            'foo/bar'                   => false,
            ''                          => false,
            'example.com'               => false,
            'http://example.com'        => true,
            '//example.com/foo/bar?s=q' => true,
            'https://example.com'       => true,
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Url::fromString($original)->isAbsolute());
        }
    }

    /** @test */
    public function instance_can_be_printed_as_string()
    {
        $this->assertEquals('foobar.com', Url::fromString('foobar.com'));
    }

    /** @test */
    public function it_throws_exception_if_url_cannot_be_parsed()
    {
        $this->expectException(InvalidUrl::class);

        Url::fromString('javascript://');
    }
}
