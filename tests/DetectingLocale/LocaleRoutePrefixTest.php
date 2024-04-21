<?php

namespace Thinktomorrow\Locale\Tests\DetectingLocale;

use Thinktomorrow\Locale\Tests\TestCase;

class LocaleRoutePrefixTest extends TestCase
{
    public function test_locale_route_prefix_gives_you_the_active_segment()
    {
        $this->detectLocaleAfterVisiting('http://unknown.com/');
        $this->assertEquals('/', localeRoutePrefix());
        $this->assertEquals('locale-zero', app()->getLocale());

        $this->detectLocaleAfterVisiting('http://unknown.com/segment-four');
        $this->assertEquals('segment-four', localeRoutePrefix());
        $this->assertEquals('locale-four', app()->getLocale());
    }
}
