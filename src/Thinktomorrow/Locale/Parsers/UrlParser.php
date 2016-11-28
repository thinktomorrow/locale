<?php

namespace Thinktomorrow\Locale\Parsers;

use Thinktomorrow\Locale\Locale;

class UrlParser
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var array
     */
    private $parsed;

    private $schemeless = false;

    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
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

        return $this->reassemble($this->parsed);
    }

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar
     *
     * @param null $locale
     * @return string
     */
    public function localize($locale = null)
    {
        $this->localizePath($locale);

        return $this;
    }

    private function localizePath($locale = null)
    {
        $this->assertUrlExists();

        $this->parsed['path'] = str_replace('//','/',
            '/'.$this->locale->getSlug($locale).$this->delocalizePath()
        );
    }

    /**
     * @return array
     */
    private function delocalizePath()
    {
        $this->assertUrlExists();

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