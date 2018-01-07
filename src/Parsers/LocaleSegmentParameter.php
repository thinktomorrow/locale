<?php

namespace Thinktomorrow\Locale\Parsers;

use Thinktomorrow\Locale\Scope;

class LocaleSegmentParameter
{
    /**
     * Isolate locale value from parameters.
     *
     * @param Scope $scope
     * @param string $routeKey
     * @param array $parameters
     * @return null|string
     */
    public static function extractLocaleSegmentFromParameters(Scope $scope, string $routeKey, array &$parameters = [])
    {
        $localeSegment = null;

        // If none given, we should be returning the current active locale segment
        // If value is explicitly null, we assume the current locale is expected
        if (!array_key_exists($routeKey, $parameters) || is_null($parameters[$routeKey])) {
            return $scope->activeSegment();
        }

        if ($scope->validateSegment($parameters[$routeKey])) {
            $localeSegment = $parameters[$routeKey];
        }

        // If locale segment parameter is not a 'real' parameter, we ignore this value and use the current locale instead
        // The 'wrong' parameter will be passed along but without key
        if ($localeSegment != $parameters[$routeKey]) {
            $parameters[] = $parameters[$routeKey];
        }

        unset($parameters[$routeKey]);

        return $localeSegment;
    }

    /**
     * @param Scope $scope
     * @param string $routeKey
     * @param $locale
     * @return array|null|Values\Locale
     */
    public static function normalizeLocaleAsParameter(Scope $scope, string $routeKey, $locale)
    {
        if (!is_array($locale)) {

            // You should provide the actual locale but in case the segment value is passed
            // we allow for this as well and normalize it to the expected locale value.
            if (!$scope->validateLocale($locale) && $scope->validateSegment($locale)) {
                $locale = $scope->findLocale($locale);
            }

            // Locale should be passed as second parameter but in case it is passed as array
            // alongside other parameters, we will try to extract it
            if ($scope->validateLocale($locale)) {
                $locale = [$routeKey => $scope->segment($locale)];
            }
        }

        return (array)$locale;
    }
}