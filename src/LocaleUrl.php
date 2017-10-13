<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\Services\Config;

class LocaleUrl
{
    /**
     * @var Scope
     */
    private $scope;

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

    public function __construct(Detect $detect, UrlParser $urlparser, RouteParser $routeparser, Config $config)
    {
        $this->scope = $detect->getScope(); // TODO check if this still returns proper results when loading before routes
        $this->urlparser = $urlparser;
        $this->routeparser = $routeparser;

        $this->placeholder = $config->get('placeholder');
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
        if(is_bool($secure)) $this->urlparser->secure($secure);

        /**
         * Convert locale to segment
         */
        return $this->urlparser->set($url)
                            ->locale($this->scope->segment($locale), $this->scope->all())
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
     *
     * @return mixed
     */
    public function route($name, $locale = null, $parameters = [])
    {
       if (!is_array($locale)){

            // You should provide the actual locale but in case the segment value is passed
            // we allow for this as well and normalize it to the expected locale value.
            if(! $this->scope->validate($locale) && $this->scope->validateSegment($locale))
            {
                $locale = $this->scope->get($locale);
            }

           // Locale should be passed as second parameter but in case it is passed as array
           // alongside other parameters, we will try to extract it
           if($this->scope->validate($locale))
            {
                $locale = [$this->placeholder => $this->scope->segment($locale)];
            }
        }

        $parameters = array_merge((array)$locale, (array) $parameters);
        $localeSegment = $this->extractLocaleSegmentFromParameters($parameters);

        return $this->routeparser->set($name)
                            ->locale($localeSegment, $this->scope->all())
                            ->parameters($parameters)
                            ->get();
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

        if($this->scope->validateSegment($parameters[$this->placeholder]))
        {
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
}
