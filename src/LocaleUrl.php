<?php

namespace Thinktomorrow\Locale;

use Illuminate\Routing\Route;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\CanonicalScope;
use Thinktomorrow\Locale\ScopeCollection;
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
    private $placeholder;

    /**
     * @var RouteParser
     */
    private $routeparser;

    public function __construct(DetectLocaleAndScope $detect, UrlParser $urlparser, RouteParser $routeparser, Config $config)
    {
        $this->scope = $detect->getScope(); // TODO check if this still returns proper results when loading before routes
        $this->scopeCollection = ScopeCollection::fromConfig($config);
        $this->urlparser = $urlparser;
        $this->routeparser = $routeparser;

        $this->placeholder = $config->get('placeholder');
    }

    /**
     * Generate a localized url.
     *
     * @param $url
     * @param null $locale
     * @param array $parameters
     * @param null $secure
     *
     * @return mixed
     */
    public function to($url, $locale = null, $parameters = [], $secure = null)
    {
        if (is_bool($secure)) {
            $this->urlparser->secure($secure);
        }

        /**
         * Convert locale to segment
         */
        return $this->urlparser->set($url)
            ->locale($this->scope->segment($locale), $this->scope->locales())
            ->parameters($parameters)
            ->get();
    }

    /**
     * Generate a localized route.
     * Note that unlike the Illuminate route() no parameter for 'absolute' path is available
     * since urls will always be rendered as absolute ones.
     *
     * @param $name
     * @param null $locale
     * @param array $parameters
     * @param bool $asCanonical
     * @return mixed
     */
    public function route($name, $locale = null, $parameters = [], $asCanonical = false)
    {
        // TODO: what if dev wants to get localized route for locale outside of current scope?
        // e.g. route('foo.bar','en'); - how we know which en to take? Default to canonical else to first matching scope?

        $parameters = array_merge($this->normalizeLocaleAsParameter($locale), (array)$parameters);
        $localeSegment = $this->extractLocaleSegmentFromParameters($parameters);

        $parser = $this->routeparser->set($name, $parameters)
            ->locale($localeSegment, $this->scope->locales());

        if ($asCanonical) {
            $parser = $this->withCanonicalScope($parser, $locale, function($parser){
                if( ! $this->scope->customRoot()) return $parser;
                return $parser->setCustomRoot($this->scope->customRoot());
            });
        }

        return $parser->get();
    }

    public function canonicalRoute($name, $locale = null, $parameters = [])
    {
        return $this->route($name, $locale, $parameters, true);
    }

    private function withCanonicalScope(RouteParser $parser, $locale = null, callable $routeCallback): RouteParser
    {
        /**
         * Freeze the current scope so that after the temporary switch into
         * the other canonical scope, we can safely return to the current.
         */
        $scopeOnIce = $this->scope;

        if($canonicalScope = $this->scopeCollection->findCanonical($locale ?? $this->scope->activeLocale()))
        {
            $this->scope = $canonicalScope;
        }

        $parser = call_user_func_array($routeCallback, [$parser]);
        //$parsedRoute = $this->route($name, $locale, $parameters, !!$canonicalScope);

        $this->scope = $scopeOnIce;

        return $parser;
    }

    /**
     * Isolate locale value from parameters.
     *
     * @param array $parameters
     *
     * @return null|string
     */
    private function extractLocaleSegmentFromParameters(array &$parameters = [])
    {
        $localeSegment = null;

        // If none given, we should be returning the current active locale segment
        // If value is explicitly null, we assume the current locale is expected
        if (!array_key_exists($this->placeholder, $parameters) || is_null($parameters[$this->placeholder])) {
            return $this->scope->activeSegment();
        }

        if ($this->scope->validateSegment($parameters[$this->placeholder])) {
            $localeSegment = $parameters[$this->placeholder];
        }

        // If locale segment parameter is not a 'real' parameter, we ignore this value and use the current locale instead
        // The 'wrong' parameter will be passed along but without key
        if ($localeSegment != $parameters[$this->placeholder]) {
            $parameters[] = $parameters[$this->placeholder];
        }

        unset($parameters[$this->placeholder]);

        return $localeSegment;
    }

    /**
     * @param $locale
     * @return array|null|Values\Locale
     */
    private function normalizeLocaleAsParameter($locale)
    {
        if (!is_array($locale)) {

            // You should provide the actual locale but in case the segment value is passed
            // we allow for this as well and normalize it to the expected locale value.
            if (!$this->scope->validateLocale($locale) && $this->scope->validateSegment($locale)) {
                $locale = $this->scope->findLocale($locale);
            }

            // Locale should be passed as second parameter but in case it is passed as array
            // alongside other parameters, we will try to extract it
            if ($this->scope->validateLocale($locale)) {
                $locale = [$this->placeholder => $this->scope->segment($locale)];
            }
        }

        return (array)$locale;
    }
}
