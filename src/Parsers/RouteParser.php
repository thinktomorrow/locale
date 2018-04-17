<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Contracts\Translation\Translator;
use Thinktomorrow\Locale\Values\Root;

class RouteParser
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
    private $customRoot;

    /** @var Translator */
    private $translator;

    /** @var UrlParser */
    private $urlParser;

    public function __construct(UrlParser $urlParser, Translator $translator)
    {
        $this->urlParser = $urlParser;
        $this->translator = $translator;
    }

    public function get(): string
    {
        $translationKey = config('thinktomorrow.locale.routes_filename').'.'.$this->routename;

        $url = $this->translator->get($translationKey, [], $this->locale);

        // If route translation does not exist, laravel returns us back the langkey.
        $url = ($url == $translationKey)
            ? $this->resolveRoute($this->routename, $this->parameters)
            : UriParameters::replace($url, $this->parameters);

        $parser = $this->urlParser->set($url)->secure($this->secure)->localize($this->localeSegment, $this->available_locales);

        if ($this->customRoot) {
            $parser->setCustomRoot($this->customRoot);
        }

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
        $this->customRoot = null;
        $this->secure = null;
        $this->parameters = [];
        $this->locale = null;
        $this->localeSegment = null;
    }

    public function setCustomRoot(Root $customRoot)
    {
        $this->customRoot = $customRoot;

        return $this;
    }

    public function localize(string $localeSegment = null, array $available_locales): self
    {
        $this->localeSegment = $localeSegment;
        $this->available_locales = $available_locales;

        // Our route translator requires the corresponding locale
        $this->locale = (!$localeSegment || $localeSegment == '/')
            ? $available_locales['/']
            : $available_locales[$localeSegment];

        return $this;
    }

    public function resolveRoute($routekey, $parameters = [])
    {
        return $this->urlParser->resolveRoute($routekey, $parameters, true);
    }
}
