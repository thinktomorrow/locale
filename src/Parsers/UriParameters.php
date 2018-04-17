<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UriParameters
{
    /**
     * Replace route parameters.
     *
     * @param $uri
     * @param array $parameters
     * @return mixed|string
     */
    public static function replace($uri, $parameters = [])
    {
        $parameters = (array) $parameters;

        $uri = static::replaceRouteParameters($uri, $parameters);
        $uri = str_replace('//', '/', $uri);

        return $uri;
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @note: taken from the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    private static function replaceRouteParameters($path, array $parameters)
    {
        $path = static::replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && !Str::endsWith($match[0], '?}'))
                ? $match[0]
                : array_shift($parameters);
        }, $path);

        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }

    /**
     * Replace all of the named parameters in the path.
     *
     * @note: taken from the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    private static function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
        }, $path);
    }
}