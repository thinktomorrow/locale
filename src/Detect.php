<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Detectors\FallbackDetector;
use Thinktomorrow\Locale\Detectors\HiddenSegmentDetector;
use Thinktomorrow\Locale\Detectors\QueryDetector;
use Thinktomorrow\Locale\Detectors\SegmentDetector;
use Thinktomorrow\Locale\Values\ApplicationLocale;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

final class Detect
{
    private Request $request;
    private Config $config;

    /** Current scope of locales */
    private ?Scope $scope = null;
    private ?Locale $locale = null;

    public function __construct(Request $request, Config $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Detect the locale from current request url.
     * Once the locale has been determined, it will be set as the application locale.
     * A locale is only validated if it is present within the current locale scope.
     *
     * Detection honours following priority:
     * 1) If locale is found in request as query parameter e.g. ?locale=fr,
     * 2) If locale is found in request url, either from host or segment eg. nl.example.com, example.nl or example.com/nl
     * 3) Otherwise set locale to our fallback language (app.locale)
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

        foreach ($detectors as $detector) {
            $locale = app($detector)->get($this->getScope(), $this->config) ?? $locale;
        }

        if ($locale) {
            $this->locale = $locale;
            Scope::setActiveLocale($locale);
            $this->setApplicationLocale();
        }

        return $this;
    }

    public function getLocale(): Locale
    {
        if (! $this->locale) {
            $this->detectLocale();
        }

        return $this->locale;
    }

    public function getScope(): Scope
    {
        if (! $this->scope) {
            $this->detectScope();
        }

        return $this->scope;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * This is handy for setting allowed scope via other source than config file.
     * this can be configurable from a cms.
     */
    public function setScope(?Scope $scope = null): self
    {
        if ($scope) {
            $this->scope = $scope;
        }

        return $this;
    }

    private function detectScope(): void
    {
        $this->scope = ScopeRepository::fromConfig($this->config)->findByRoot($this->request->root());
    }

    private function setApplicationLocale(): void
    {
        $applicationLocale = ApplicationLocale::from($this->locale);

        app()->setLocale($applicationLocale->__toString());
    }
}
