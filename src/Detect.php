<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Detectors\HiddenSegmentDetector;
use Thinktomorrow\Locale\Detectors\QueryDetector;
use Thinktomorrow\Locale\Detectors\SegmentDetector;
use Thinktomorrow\Locale\Services\Config;
use Thinktomorrow\Locale\Services\Scope;
use Thinktomorrow\Locale\Services\ScopeHub;

final class Detect
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * Current scope of locales
     * @var Scope
     */
    private $scope;

    public function __construct(Request $request, Config $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Detect the locale for current request
     *
     * The locale is determined based on the given request url. Locales are available per domain scope which behave
     * according to the config settings.
     *
     * Detection follows this priority
     * // 0) If locale is passed as parameter, this locale will be forced
     * 1) If locale is in request as query parameter e.g. ?locale=fr,
     * 2) If locale is found in request url eg. nl.example.com, example.nl or example.com/nl
     * // 3) Default: get locale from cookie
     * 4) Otherwise: set locale to our fallback language
     *
     * @return string
     */
    public function get(): string
    {
        $locale = null;

        if(!$this->scope) $this->detectScope();

        $detectors = [
            FallbackDetector::class,
            HiddenSegmentDetector::class,
            SegmentDetector::class,
            QueryDetector::class,
        ];

        foreach($detectors as $detector)
        {
            $locale = app($detector)->get($this->scope, $this->config) ?? $locale;
        }

        app()->setLocale($locale->get());

        return $locale->get();
    }

    public function forceScope(Scope $scope)
    {
        $this->scope = $scope;
    }

    private function detectScope()
    {
        $this->scope = ScopeHub::fromArray($this->config)
                                ->findByHost($this->request->getHost());
    }
}
