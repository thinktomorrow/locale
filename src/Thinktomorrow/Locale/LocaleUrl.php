<?php

namespace Thinktomorrow\Locale;

use Illuminate\Contracts\Routing\UrlGenerator;

class LocaleUrl
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var Illuminate\Contracts\Routing\UrlGenerator
     */
    private $generator;

    /**
     * @var null|string
     */
    private $placeholder;

    public function __construct(Locale $locale, UrlGenerator $generator, $config = [])
    {
        $this->locale = $locale;
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
        $url = $this->prependLocaleToUri($url,$locale);

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
        $locale = $this->extractLocaleFromParameter($parameters);

        $url = $this->resolveRoute($name,$parameters,$absolute);

        return $this->to($url,$locale);
    }

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar
     *
     * @param $url
     * @param null $locale
     * @return string
     */
    private function prependLocaleToUri($url, $locale = null)
    {
        $locale = $this->locale->getSlug($locale);
        $parsed = parse_url($url);

        $path = $this->cleanPathFromExistingLocale($parsed);

        $parsed['path'] = str_replace('//','/','/'.$locale.$path);

        return $this->reassembleParsedUrl($parsed);
    }

    /**
     * Isolate locale value from parameters
     *
     * @param array $parameters
     * @return null|string
     */
    private function extractLocaleFromParameter(&$parameters = [])
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
     * Construct a full url with the parsed url elements
     * resulted from a parse_url() function call
     *
     * @param array $parsed
     * @return string
     */
    private function reassembleParsedUrl(array $parsed)
    {
        return
            ((isset($parsed['scheme'])) ? $parsed['scheme'] . '://' : '')
            .((isset($parsed['user'])) ? $parsed['user'] . ((isset($parsed['pass'])) ? ':' . $parsed['pass'] : '') .'@' : '')
            .((isset($parsed['host'])) ? $parsed['host'] : '')
            .((isset($parsed['port'])) ? ':' . $parsed['port'] : '')
            .((isset($parsed['path'])) ? $parsed['path'] : '')
            .((isset($parsed['query'])) ? '?' . $parsed['query'] : '')
            .((isset($parsed['fragment'])) ? '#' . $parsed['fragment'] : '');
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

    /**
     * @param $parsed
     * @return array
     */
    private function cleanPathFromExistingLocale($parsed)
    {
        if (!isset($parsed['path'])) return null;

        $path_segments = explode('/', trim($parsed['path'], '/'));

        if (count($path_segments) < 1) return null;

        if ($path_segments[0] == $this->locale->getSlug($path_segments[0]) || $this->locale->isHidden($path_segments[0])) {
            unset($path_segments[0]);
        }

        return '/' . implode('/', $path_segments);
    }

}