<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Routing\UrlGenerator;
use Thinktomorrow\Url\Root;
use Thinktomorrow\Url\Url;

class UrlParser
{
    private UrlGenerator $generator;

    private Url $url;
    private ?string $localeSegment = null;
    private array $available_locales = [];
    private ?bool $secure = null;
    private array $parameters = [];

    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;

        /*
         * Default url is the root as given by the application
         */
        $this->url = $this->rootFromApplication();
    }

    public function get(): string
    {
        if($this->url->hasHost()) {
            if ($this->secure === true) {
                $this->url->secure();
            } elseif($this->secure === false) {
                $this->url->nonSecure();
            }
        }

        // Only when a relative url is given, the parameters are added to the url
        return $this->generator->to(
            $this->url->localize($this->localeSegment, $this->available_locales)->get(),
            $this->parameters,
            $this->secure
        );
    }

    public function set(string $url): self
    {
        $this->url = Url::fromString($url);

        return $this;
    }

    public function setCustomRoot(Root $root)
    {
        $this->url->setCustomRoot($root);

        return $this;
    }

    public function localize(string $localeSegment = null, array $available_locales): self
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

    public function secure(bool $secure = true): self
    {
        $this->secure = $secure;

        return $this;
    }

    private function rootFromApplication(): Url
    {
        return Url::fromString($this->generator->formatRoot($this->generator->formatScheme($this->secure)));
    }

    /**
     * Resolve the route via the Illuminate UrlGenerator.
     *
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
