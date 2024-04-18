<?php

use Thinktomorrow\Locale\LocaleUrl;

if (! function_exists('localeurl')) {
    /**
     * @param $url
     * @param null  $locale
     * @param array $extra
     * @param null  $secure
     */
    function localeurl($url, $locale = null, $extra = [], $secure = null): string
    {
        return app(LocaleUrl::class)->to($url, $locale, $extra, $secure);
    }
}

if (! function_exists('localeroute')) {
    /**
     * @param $name
     * @param null  $locale
     * @param array $parameters
     * @param bool  $asCanonical
     */
    function localeroute($name, $locale = null, $parameters = [], $asCanonical = true): string
    {
        return app(LocaleUrl::class)->route($name, $locale, $parameters, $asCanonical);
    }
}

/*
 * Detect locale and return the active segment. Useful for route segment injections
 */
if (! function_exists('localeRoutePrefix')) {
    function localeRoutePrefix()
    {
        return app(\Thinktomorrow\Locale\Detect::class)
                    ->detectLocale()
                    ->getScope()
                    ->activeSegment();
    }
}

/*
 * Get the sanitized locale config which gives you access to the canonicals
 */
if (! function_exists('localeConfig')) {
    function localeConfig()
    {
        return app(\Thinktomorrow\Locale\Detect::class)->getConfig();
    }
}
