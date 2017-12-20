<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Thinktomorrow\Locale\Values\Root;

class RouteParserContract implements ParserContract
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
    private $secure = null;

    /** @var array */
    private $parameters = [];

    /** @var Root */
    private $forcedRoot;

    /** @var Translator */
    private $translator;

    /**
     * @var UrlParserContract
     */
    private $parser;

    public function __construct(UrlParserContract $parser , Translator $translator)
    {
        // TODO should be a RouteParser returning Route... so more as a factory
        $this->parser = $parser;
        $this->translator = $translator;
    }

    public function get(): string
    {
        $routekey = $this->translator->get('routes.'.$this->routename, [], $this->locale);

        $url = ($routekey === 'routes.'.$this->routename)
            ? $this->resolveRoute($this->routename, $this->parameters)
            : $this->replaceParameters($routekey, $this->parameters);

        $parser = $this->parser->set($url)->secure($this->secure)->locale($this->localeSegment, $this->available_locales);

        if($this->forcedRoot) $parser->forceRoot($this->forcedRoot);

        return $parser->get();
    }

    public function set(string $routename, array $parameters = [], $secure = null)
    {
        $this->reset();

        $this->routename = $routename;
        $this->parameters = $parameters;
        $this->secure = $secure;

        return $this;
    }

    private function reset()
    {
        $this->routename = null;
        $this->forcedRoot = null;
        $this->secure = null;
        $this->parameters = [];
        $this->locale = null;
        $this->localeSegment = null;
    }

//    public function set(string $routename): self
//    {
//        $this->routename = $routename;
//
//        return $this;
//    }

    public function forceRoot(Root $root)
    {
        $this->forcedRoot = $root;

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

//    public function parameters(array $parameters = []): self
//    {
//        $this->parameters = $parameters;
//
//        return $this;
//    }
//
//    public function secure($secure = true): self
//    {
//        $this->secure = (bool) $secure;
//
//        return $this;
//    }

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
