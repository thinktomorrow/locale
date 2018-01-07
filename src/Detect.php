<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Detectors\FallbackDetector;
use Thinktomorrow\Locale\Detectors\HiddenSegmentDetector;
use Thinktomorrow\Locale\Detectors\QueryDetector;
use Thinktomorrow\Locale\Detectors\SegmentDetector;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

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
     * @var Locale
     */
    private $locale;

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
     * @return self
     */
    public function detectLocale(): self
    {
        $locale = null;

        $detectors = [
            FallbackDetector::class,
            HiddenSegmentDetector::class,
            SegmentDetector::class,
            QueryDetector::class,
        ];

        foreach($detectors as $detector)
        {
            $locale = app($detector)->get($this->getScope(), $this->config) ?? $locale;
        }

        $this->locale = $locale;

        app()->setLocale($locale->get());

        return $this;
    }

    public function getLocale(): Locale
    {
        if( ! $this->locale ) $this->detectLocale();

        return $this->locale;
    }

    public function getScope(): Scope
    {
        if( ! $this->scope ) $this->detectScope();

        return $this->scope;
    }

    /**
     * This is handy for setting allowed scope via other source than config file.
     * TODO: we should allow to set config from other source such as db as well so
     * this can be configurable from a cms.
     *
     * @param Scope|null $scope
     * @return $this
     */
    public function setScope(Scope $scope = null)
    {
        if($scope) $this->scope = $scope;

        return $this;
    }

    private function detectScope()
    {
        $this->scope = ScopeCollection::fromConfig($this->config)->findByRoot($this->request->getHost());
    }
}
