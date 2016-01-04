<?php

namespace Thinktomorrow\Locale;

use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use InvalidArgumentException;

class UrlGenerator extends BaseUrlGenerator
{
    /**
     * Flag if route should be localized
     *
     * @var null
     */
    private $locale_enabled;

    /**
     * Representation of the locale element as route placeholder
     * The {<placeholder>} surroundings should not be added.
     *
     * @var string
     */
    private $locale_slug;

    public function route($name, $parameters = [], $absolute = true)
    {
        if (! is_null($route = $this->routes->getByName($name))) {

            $this->getLocaleSlug();

            if($this->shouldLocaleBeInjected($route))
            {
                if(!is_array($parameters)) $parameters = [$parameters];

                // Locale slug could be passed manually which should have priority
                if(!isset($parameters[$this->locale_slug]))
                {
                    $locale = app()->make(Locale::class);

                    $parameters[$this->locale_slug] = $locale->get();

                    if($locale->isNaked())
                    {
                        // If null value is passed, the route parameter will essentially be ignored
                        $parameters[$this->locale_slug] = null;
                    }
                }
            }

            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }

    private function shouldLocaleBeInjected(Route $route)
    {
        if(!$this->isLocaleEnabled()) return false;

        if(false !== strpos($route->uri(),'{'.$this->locale_slug.'}')) return true;

        if($action = $route->getAction() and isset($action['domain']) and false !== strpos($action['domain'],'{'.$this->locale_slug.'}')) return true;

        return false;
    }

    private function isLocaleEnabled()
    {
        if(null !== $this->locale_enabled) return $this->locale_enabled;

        return $this->locale_enabled = config('thinktomorrow.locale.enable',true);
    }

    private function getLocaleSlug()
    {
        if(null !== $this->locale_slug) return $this->locale_slug;

        return $this->locale_slug = config('thinktomorrow.locale.locale_slug','locale_slug');
    }
}