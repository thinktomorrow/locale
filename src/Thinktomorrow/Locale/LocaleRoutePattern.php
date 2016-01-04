<?php

namespace Thinktomorrow\Locale;

use Illuminate\Routing\Router;

class LocaleRoutePattern
{
    /**
     * @var Router
     */
    private $router;
    private $available_locales;
    private $locale_slug_key;

    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->available_locales = config('thinktomorrow.locale.available_locales',[]);
        $this->locale_slug_key = config('thinktomorrow.locale.locale_slug','locale_slug');
    }

    /**
     * @param Router $router
     * @param $available_locales
     * @param $locale_slug_key
     */
    public function provide()
    {
        $pattern = '(' . implode('|', $this->available_locales) . ')';

        $this->router->pattern($this->locale_slug_key, $pattern);
    }
}