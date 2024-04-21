<?php

namespace Thinktomorrow\Locale\Tests\DetectingLocale;

use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Facades\ScopeFacade;
use Thinktomorrow\Locale\Tests\TestCase;

class DetectScopeTest extends TestCase
{
    public function test_it_detects_default_scope_when_nothing_matches()
    {
        $this->detectLocaleAfterVisiting('http://unknown.com/');

        $this->assertEquals($this->getDefaultScope(), app(Detect::class)->getScope());
        $this->assertEquals('locale-zero', ScopeFacade::activeLocale());
        $this->assertEquals('/', ScopeFacade::activeSegment());
    }

    public function test_detect_scope_from_request()
    {
        $this->detectLocaleAfterVisiting('http://example.com/segment-one');

        $this->assertEquals('locale-one', app(Detect::class)->getScope()->activeLocale());
        $this->assertEquals('locale-one', ScopeFacade::activeLocale());
        $this->assertEquals('segment-one', app(Detect::class)->getScope()->activeSegment());
        $this->assertEquals('segment-one', ScopeFacade::activeSegment());
    }
}
