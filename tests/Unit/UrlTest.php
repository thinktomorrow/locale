<?php

namespace Thinktomorrow\Locale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Locale\Services\Url;

class UrlTest extends TestCase
{
    /** @test */
    function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Url::class, Url::fromString('fake'));
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
            $this->assertEquals($url, Url::fromString($url)->get());
        }
    }

    /** @test */
    public function it_accepts_a_locale_path_segment()
    {
        $urls = [
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
    public function it_can_set_a_hidden_locale_path_segment()
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
    public function it_keeps_passed_root_if_not_set_explicitly()
    {
        $this->assertEquals('http://example.fr/fr/foo/bar',
            Url::fromString('http://example.fr/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
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
}