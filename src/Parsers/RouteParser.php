<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Contracts\Translation\Translator;
use Thinktomorrow\Locale\Values\ApplicationLocale;
use Thinktomorrow\Url\Root;
use Thinktomorrow\Url\Url;

class RouteParser
{
    private Translator $translator;
    private UrlParser $urlParser;

    private ?string $routename = null;
    private ?string $locale = null;
    private ?string $localeSegment = null;
    private array $available_locales = [];
    private ?bool $secure = null;
    private array $parameters = [];
    private ?Root $customRoot = null;

    public function __construct(UrlParser $urlParser, Translator $translator)
    {
        $this->urlParser = $urlParser;
        $this->translator = $translator;
    }

    public function get(): string
    {
        $translationKey = config('locale.routes_filename').'.'.$this->routename;

        $url = $this->translator->get($translationKey, [], $this->locale);

        // If route translation does not exist, laravel returns us back the langkey.
        $url = ($url == $translationKey)
            ? $this->resolveRoute($this->routename, $this->parameters)
            : UriParameters::replace($url, $this->parameters);

        $secure = $this->secure ?? Url::fromString($url)->isSecure();

        $parser = $this->urlParser->set($url)->secure($secure)->localize($this->localeSegment, $this->available_locales);

        if ($this->customRoot) {
            $parser->setCustomRoot($this->customRoot);
        }

        return $parser->get();
    }

    public function set(string $routename, array $parameters = [], $secure = null)
    {
        $this->reset();

        $this->routename = $routename;
        $this->parameters = SanitizeParameters::rawurlencode($parameters);
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

    public function localize(?string $localeSegment, array $available_locales): self
    {
        $this->localeSegment = $localeSegment;
        $this->available_locales = $available_locales;

        // Our route translator requires the corresponding application locale
        $locale = (! $localeSegment || $localeSegment == '/')
            ? $available_locales['/']
            : $available_locales[$localeSegment];

        $this->locale = ApplicationLocale::from($locale)->__toString();

        return $this;
    }

    public function resolveRoute($routekey, $parameters = [])
    {
        return $this->urlParser->resolveRoute($routekey, $parameters);
    }
}
