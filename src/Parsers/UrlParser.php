<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Routing\UrlGenerator;
use Thinktomorrow\Locale\Services\Root;
use Thinktomorrow\Locale\Services\Url;

class UrlParser implements Parser
{
    /** @var Url */
    private $url;

    /** @var string */
    private $localeSegment = null;

    /** @var array */
    private $available_locales = [];

    /** @var bool */
    private $secure;

    /** @var array */
    private $parameters = [];

    /** @var UrlGenerator */
    private $generator;

    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;

        /**
         * Default url is the root as given by the application
         */
        $this->url = $this->rootFromApplication();
    }

    public function get(): string
    {
        if(is_bool($this->secure)) $this->url->secure($this->secure);

        return $this->generator->to(
            $this->url->localize($this->localeSegment, $this->available_locales)->get(), $this->parameters, $this->secure
        );
    }

    public function set(string $url): self
    {
        // TODO Prepend current root if given url is a relative path
        $this->url = Url::fromString($url);

        return $this;
    }

    public function locale(string $localeSegment = null, array $available_locales): self
    {
        $this->localeSegment = $localeSegment;
        $this->available_locales = $available_locales;

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

    private function rootFromApplication(): Url
    {
        return Url::fromString($this->generator->formatRoot($this->generator->formatScheme($this->secure)));
    }

    /**
     * Resolve the route via the Illuminate UrlGenerator.
     * TODO: this should be part of the RouteParser no?
     * @param $routekey
     * @param array $parameters
     *
     * @return string
     */
    public function resolveRoute($routekey, $parameters = [])
    {
        return $this->generator->route($routekey, $parameters, true);
    }
}
