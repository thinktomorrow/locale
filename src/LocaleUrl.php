<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Parsers\LocaleSegmentParameter;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Locale;

class LocaleUrl
{
    private Scope $scope;
    private ScopeCollection $scopeCollection;

    private UrlParser $urlparser;
    private RouteParser $routeparser;

    private ?string $routeKey;
    private bool $forceSecure;

    public function __construct(Detect $detect, UrlParser $urlparser, RouteParser $routeparser, Config $config)
    {
        // TODO check if this still returns proper results when loading before routes
        $this->scope = $detect->getScope();
        $this->scopeCollection = ScopeCollection::fromConfig($config);
        $this->urlparser = $urlparser;
        $this->routeparser = $routeparser;

        $this->routeKey = $config->get('route_key');
        $this->forceSecure = $config->get('secure') === true;
    }

    /**
     * Generate a localized url.
     */
    public function to(string $url, $locale = null, string|array $parameters = [], ?bool $secure = null): string
    {
        if (is_bool($secure)) {
            $this->urlparser->secure($secure);
        } elseif ($this->forceSecure) {
            $this->urlparser->secure();
        }

        return $this->urlparser->set($url)
            ->localize($this->scope->segment($locale), $this->scope->locales())
            ->parameters((array) $parameters)
            ->get();
    }

    /**
     * Generate a localized route.
     * Note that unlike the Illuminate route() no parameter for 'absolute' path is available
     * since urls will always be rendered as absolute ones.
     */
    public function route($name, null|string|array $locale = null, null|string|array $parameters = [], bool $asCanonical = false): string
    {
        $scope = $this->scope;
        $forceSecure = $this->forceSecure;
        $available_locales = $scope->locales();

        if ($asCanonical && $canonicalScope = $this->getCanonicalScope($locale)) {
            $scope = $canonicalScope;

            /**
             * In current scope the prefix is /test/ for fr but in passed canonical scope it is the default /
             * In canonical scope we have no knowledge of this being a locale segment so it is not removed by the parser.
             */
            $available_locales = array_merge($available_locales, $canonicalScope->locales());

            /**
             * Canonical that has no scheme will be forced as secure if set so in config.
             * If an explicit scheme is given, this is left unmodified in case of canonicals.
             */
            $forceSecure = $scope->customRoot() && $scope->customRoot()->scheme() ? false : $forceSecure;
        }

        $parameters = array_merge(LocaleSegmentParameter::normalizeLocaleAsParameter($scope, $this->routeKey, $locale), (array) $parameters);
        $localeSegment = LocaleSegmentParameter::extractLocaleSegmentFromParameters($scope, $this->routeKey, $parameters);

        $parser = $this->routeparser->set($name, $parameters, ($forceSecure) ? true : null)
            ->localize($localeSegment, $available_locales);

        if ($asCanonical) {
            $parser = $this->parseWithCanonicalScope($scope, $parser);
        }

        return $parser->get();
    }

    public function canonicalRoute(string $name, $locale = null, $parameters = []): string
    {
        return $this->route($name, $locale, $parameters, true);
    }

    private function getCanonicalScope(null|string $locale = null): ?Scope
    {
        if ($canonicalScope = $this->scopeCollection->findCanonical($locale ?? $this->scope->activeLocale())) {
            return $canonicalScope;
        }

        return null;
    }

    private function parseWithCanonicalScope(Scope $scope, RouteParser $parser): RouteParser
    {
        if (! $scope->customRoot()) {
            return $parser;
        }

        return $parser->setCustomRoot($scope->customRoot());
    }
}
