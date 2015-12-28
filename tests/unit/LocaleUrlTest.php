<?php

namespace Thinktomorrow\Locale\Tests;

use TestCase;
use Thinktomorrow\Locale\Locale;
use Thinktomorrow\Locale\LocaleUrl;

class LocaleUrlTest extends TestCase
{
    /** @test */
    public function it_can_be_called()
    {
        $locale = app()->make(LocaleUrl::class);

        $this->assertInstanceOf(LocaleUrl::class,$locale);
    }

    /** @test */
    public function it_can_create_a_localized_url()
    {
        $locale = app()->make(LocaleUrl::class);

        $urls = [
            '/foo/bar'                            => '/nl/foo/bar',
            'foo/bar'                             => 'nl/foo/bar',
            ''                                    => '/nl',
            'http://example.com'                  => 'http://example.com/nl',
            'http://example.com/foo/bar'          => 'http://example.com/nl/foo/bar',
            'http://example.com/foo/bar?s=q'          => 'http://example.com/nl/foo/bar?s=q',
            'http://example.com/nl/foo/bar'          => 'http://example.com/nl/nl/foo/bar',
        ];

        foreach($urls as $original => $result)
        {
            $this->assertEquals($result,$locale->convert($original,'nl'),'improper conversion from '.$original.' to '.$locale->convert($original,'nl').' - '.$result .' was expected.');
        }
    }

}
