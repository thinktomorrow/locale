<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Routing\UrlGenerator;
use Thinktomorrow\Locale\Locale;

class UrlParser implements Parser
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * If locale is explicitly passed, we will set it
     * If null is passed it means the default locale must be used
     *
     * @var string
     */
    private $localeslug = false;

    /**
     * @var array
     */
    private $parsed;

    /**
     * @var bool
     */
    private $secure = false;

    /**
     * @var bool
     */
    private $absolute = true;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * Internal flag to keep track of schemeless url
     * @var bool
     */
    private $schemeless = false;

    /**
     * @var UrlGenerator
     */
    private $generator;

    public function __construct(Locale $locale, UrlGenerator $generator)
    {
        $this->locale = $locale;
        $this->generator = $generator;
    }

    public function set($url)
    {
        $this->parsed = parse_url($url);

        if(false === $this->parsed)
        {
            throw new \InvalidArgumentException('Failed to parse url. Invalid url ['.$url.'] passed as parameter.');
        }

        // If a schemeless url is passed, parse_url will ignore this and strip the first tags
        // so we keep a reminder to explicitly reassemble the 'anonymous scheme' manually
        $this->schemeless = (0 === strpos($url,'//') && isset($this->parsed['host']));

        return $this;
    }

    public function get()
    {
        $this->assertUrlExists();

        // Only localize the url if a locale is passed.
        if(false !== $this->localeslug) $this->localizePath($this->localeslug);

        $url = $this->reassemble($this->parsed);

        return $this->generator->to($url,$this->parameters,$this->secure);
    }

    public function resolveRoute($routekey,$parameters = [])
    {
        return $this->generator->route($routekey,$parameters,$this->absolute);
    }

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar
     *
     * @param null $localeslug
     * @return string
     */
    public function localize($localeslug = null)
    {
        $this->localeslug = $localeslug;

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function parameters(array $parameters = [])
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param bool $secure
     * @return $this
     */
    public function secure($secure = true)
    {
        $this->secure = !!$secure;

        return $this;
    }

    /**
     * @param bool $absolute
     * @return $this
     */
    public function absolute($absolute = true)
    {
        $this->absolute = !!$absolute;

        return $this;
    }

    private function localizePath($locale = null)
    {
        $this->parsed['path'] = str_replace('//','/',
            '/'.$this->locale->getSlug($locale).$this->delocalizePath()
        );
    }

    /**
     * @return array
     */
    private function delocalizePath()
    {
        if (!isset($this->parsed['path'])) return null;

        $path_segments = explode('/', trim($this->parsed['path'], '/'));

        if (count($path_segments) < 1) return null;

        if ($path_segments[0] == $this->locale->getSlug($path_segments[0]) || $this->locale->isHidden($path_segments[0])) {
            unset($path_segments[0]);
        }

        return '/' . implode('/', $path_segments);
    }

    /**
     * Construct a full url with the parsed url elements
     * resulted from a parse_url() function call
     *
     * @param array $parsed
     * @return string
     */
    private function reassemble(array $parsed)
    {
        return
            ((isset($parsed['scheme'])) ? $parsed['scheme'] . '://' : ($this->schemeless ? '//' : ''))
            .((isset($parsed['user'])) ? $parsed['user'] . ((isset($parsed['pass'])) ? ':' . $parsed['pass'] : '') .'@' : '')
            .((isset($parsed['host'])) ? $parsed['host'] : '')
            .((isset($parsed['port'])) ? ':' . $parsed['port'] : '')
            .((isset($parsed['path'])) ? $parsed['path'] : '')
            .((isset($parsed['query'])) ? '?' . $parsed['query'] : '')
            .((isset($parsed['fragment'])) ? '#' . $parsed['fragment'] : '');
    }

    private function assertUrlExists()
    {
        if (!$this->parsed) {
            throw new \LogicException('Url is required. Please run UrlParser::set($url) first.');
        }
    }
}