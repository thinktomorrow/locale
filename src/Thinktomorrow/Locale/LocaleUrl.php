<?php

namespace Thinktomorrow\Locale;

use Illuminate\Contracts\Routing\UrlGenerator;
use Thinktomorrow\Locale\Services\UrlParser;

class LocaleUrl
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var UrlParser
     */
    private $parser;

    /**
     * @var Illuminate\Contracts\Routing\UrlGenerator
     */
    private $generator;

    /**
     * @var null|string
     */
    private $placeholder;

    public function __construct(Locale $locale, UrlParser $parser, UrlGenerator $generator, $config = [])
    {
        $this->locale = $locale;
        $this->parser = $parser;
        $this->generator = $generator;

        $this->placeholder = isset($config['placeholder']) ? $config['placeholder'] : null;
    }

    /**
     * Generate a localized url
     *
     * @param $url
     * @param null $locale
     * @param array $extra
     * @param null $secure
     * @return mixed
     */
    public function to($url, $locale = null, $extra = [], $secure = null)
    {
        $url = $this->parser->set($url)
                            ->localize($locale)
                            ->get();

        return $this->resolveUrl($url, $extra, $secure);
    }

    /**
     * Generate a localized route
     *
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return mixed
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $locale = $this->extractLocaleFromParameters($parameters);

        $url = $this->resolveRoute($name,$parameters,$absolute);

        return $this->to($url,$locale);
    }

    /**
     * Isolate locale value from parameters
     *
     * @param array $parameters
     * @return null|string
     */
    private function extractLocaleFromParameters(&$parameters = [])
    {
        $locale = null;

        if(!is_array($parameters))
        {
            $locale = $this->locale->get($parameters);

            // If locale is the only parameter, we make sure the 'real' parameters is flushed
            if($locale == $parameters) $parameters = [];
        }
        elseif(!is_null($this->placeholder) && array_key_exists($this->placeholder,$parameters))
        {
            $locale = $this->locale->get($parameters[$this->placeholder]);
            unset($parameters[$this->placeholder]);
        }

        return $locale;
    }

    /**
     * Generate url via illuminate
     *
     * @param $url
     * @param array $extra
     * @param null $secure
     * @return string
     */
    private function resolveUrl($url, $extra = [], $secure = null)
    {
        return $this->generator->to($url, $extra, $secure);
    }

    /**
     * Generate route via illuminate
     *
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    private function resolveRoute($name, $parameters = [], $absolute = true)
    {
        return $this->generator->route($name, $parameters, $absolute);
    }
}