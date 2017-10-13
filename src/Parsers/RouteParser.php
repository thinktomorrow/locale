<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Thinktomorrow\Locale\Services\Root;
use Thinktomorrow\Locale\Services\Url;

class RouteParser implements Parser
{
    /** @var string */
    private $routename;

    /** @var string */
    private $locale;

    /** @var string */
    private $localeSegment = null;

    /** @var array */
    private $available_locales = [];

    /** @var bool */
    private $secure = false;

    /** @var array */
    private $parameters = [];

    /** @var Translator */
    private $translator;
    /**
     * @var UrlParser
     */
    private $parser;

    public function __construct(UrlParser $parser , Translator $translator)
    {
        $this->parser = $parser;
        $this->translator = $translator;
    }

    public function get(): string
    {
        $routekey = $this->translator->get('routes.'.$this->routename, [], $this->locale);

        $url = ($routekey === 'routes.'.$this->routename)
            ? $this->resolveRoute($this->routename, $this->parameters)
            : $this->replaceParameters($routekey, $this->parameters);

        return $this->parser->set($url)->secure($this->secure)->locale($this->localeSegment, $this->available_locales)->get();
    }

    public function set(string $routename): self
    {
        $this->routename = $routename;

        return $this;
    }

    public function locale(string $localeSegment = null, array $available_locales): self
    {
        $this->localeSegment = $localeSegment;
        $this->available_locales = $available_locales;

        // Our route translator requires the corresponding locale
        $this->locale = (!$localeSegment || $localeSegment == '/')
            ? $available_locales['/']
            : $available_locales[$localeSegment];

        return $this;
    }

    public function parameters(array $parameters = []): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function secure($secure = true): self
    {
        $this->secure = (bool) $secure;

        return $this;
    }

    public function resolveRoute($routekey, $parameters = [])
    {
        return $this->parser->resolveRoute($routekey, $parameters, true);
    }

    /**
     * Replace route parameters.
     *
     * @param $uri
     * @param array $parameters
     *
     * @return mixed|string
     */
    protected function replaceParameters($uri, $parameters = [])
    {
        $parameters = (array) $parameters;

        $uri = $this->replaceRouteParameters($uri, $parameters);
        $uri = str_replace('//', '/', $uri);

        return $uri;
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @note: based on the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceRouteParameters($path, array $parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);

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
     * @note: based on the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
        }, $path);
    }
}
