<?php

namespace Thinktomorrow\Locale;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use InvalidArgumentException;

class UrlGenerator extends BaseUrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        if (! is_null($route = $this->routes->getByName($name))) {

            if(false !== strpos($route->uri(),'{locale_slug}'))
            {
                if(!is_array($parameters)) $parameters = [$parameters];

                if(!isset($parameters['locale_slug'])) $parameters['locale_slug'] = app()->make(Locale::class)->get();
            }

            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }
}