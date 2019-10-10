<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Parsers\LocaleSegmentParameter;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\Values\Config;

class LocaleUrl
{
    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var ScopeCollection
     */
    private $scopeCollection;

    /**
     * @var UrlParser
     */
    private $urlparser;

    /**
     * @var null|string
     */
    private $routeKey;

    /**
     * @var RouteParser
     */
    private $routeparser;

    /**
     * @var bool
     */
    private $forceSecure = false;

    public function __construct(Detect $detect, UrlParser $urlparser, RouteParser $routeparser, Config $config)
    {
        // TODO check if this still returns proper results when loading before routes
        $this->scope           = $detect->getScope();
        $this->scopeCollection = ScopeCollection::fromConfig($config);
        $this->urlparser       = $urlparser;
        $this->routeparser     = $routeparser;

        $this->routeKey    = $config->get('route_key');
        $this->forceSecure = $config->get('secure') === true;
    }

    /**
     * Generate a localized url.
     *
     * @param $url
     * @param null  $locale
     * @param array $parameters
     * @param null  $secure
     *
     * @return mixed
     */
    public function to($url, $locale = null, $parameters = [], $secure = null)
    {
        if (is_bool($secure)) {
            $this->urlparser->secure($secure);
        } elseif ($this->forceSecure) {
            $this->urlparser->secure();
        }

        return $this->urlparser->set($url)
            ->localize($this->scope->segment($locale), $this->scope->locales())
            ->parameters($parameters)
            ->get();
    }

    /**
     * Generate a localized route.
     * Note that unlike the Illuminate route() no parameter for 'absolute' path is available
     * since urls will always be rendered as absolute ones.
     *
     * @param $name
     * @param null  $locale
     * @param array $parameters
     * @param bool  $asCanonical
     *
     * @return mixed
     */
    public function route($name, $locale = null, $parameters = [], $asCanonical = false)
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

    public function canonicalRoute($name, $locale = null, $parameters = [])
    {
        return $this->route($name, $locale, $parameters, true);
    }

    private function getCanonicalScope($locale = null): ?Scope
    {
        if ($canonicalScope = $this->scopeCollection->findCanonical($locale ?? $this->scope->activeLocale())) {
            return $canonicalScope;
        }

        return null;
    }

    private function parseWithCanonicalScope(Scope $scope, RouteParser $parser): RouteParser
    {
        if (!$scope->customRoot()) {
            return $parser;
        }

        return $parser->setCustomRoot($scope->customRoot());
    }
}
