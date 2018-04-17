<?php

namespace Thinktomorrow\Locale\Values;

use Thinktomorrow\Locale\Exceptions\InvalidUrl;

class Url
{
    private $parsed;
    private $root;

    private $secure;
    private $schemeless = false;
    private $absolute = false;

    private function __construct(string $url)
    {
        $this->parse($url);
    }

    public static function fromString(string $url)
    {
        return new self($url);
    }

    public function setCustomRoot(Root $root)
    {
        $this->root = $root;

        return $this;
    }

    public function secure($secure = true)
    {
        $this->secure = (bool) $secure;

        return $this;
    }

    public function get()
    {
        if ($this->root) {
            if ($this->secure) {
                $this->root->secure($this->secure);
            }

            // Path is reconstructed. Taken care of possible double slashes
            $path = str_replace('//', '/', '/'.trim($this->reassembleWithoutRoot(), '/'));
            if ($path == '/') {
                $path = '';
            }

            return $this->root->get().$path;
        }

        return $this->reassemble();
    }

    public function isAbsolute(): bool
    {
        return $this->absolute;
    }

    public function localize(string $localeSegment = null, array $available_locales = [])
    {
        $this->parsed['path'] = str_replace('//', '/',
            rtrim(
                '/'.trim($localeSegment.$this->delocalizePath($available_locales), '/'),
                '/'
            )

        );

        return $this;
    }

    private function delocalizePath(array $available_locales)
    {
        if (!isset($this->parsed['path'])) {
            return;
        }

        $path_segments = explode('/', trim($this->parsed['path'], '/'));

        // Remove the locale segment if present
        if (in_array($path_segments[0], array_keys($available_locales))) {
            unset($path_segments[0]);
        }

        return '/'.implode('/', $path_segments);
    }

    private function parse(string $url)
    {
        // Sanitize url input a bit to remove double slashes, but do not remove first slashes
        if ($url == '//') {
            $url = '/';
        }

        $this->parsed = parse_url($url);

        if (false === $this->parsed) {
            throw new InvalidUrl('Failed to parse url. Invalid url ['.$url.'] passed as parameter.');
        }

        // If a schemeless url is passed, parse_url will ignore this and strip the first tags
        // so we keep a reminder to explicitly reassemble the 'anonymous scheme' manually
        $this->schemeless = (0 === strpos($url, '//') && isset($this->parsed['host']));

        $this->absolute = (!preg_match('~^(#|//|https?://|mailto:|tel:)~', $url))
                ? filter_var($url, FILTER_VALIDATE_URL) !== false
                : true;
    }

    public function __toString(): string
    {
        return $this->get();
    }

    private function reassemble(): string
    {
        $scheme = (isset($this->parsed['scheme']))
            ? $this->parsed['scheme'].'://'
            : ($this->schemeless ? '//' : '');

        // Convert to secure scheme if needed or vice versa
        if ($scheme == 'http://' && $this->secure) {
            $scheme = 'https://';
        } elseif ($scheme == 'https://' && false === $this->secure) {
            $scheme = 'http://';
        }

        return
            $scheme
            .((isset($this->parsed['user'])) ? $this->parsed['user'].((isset($this->parsed['pass'])) ? ':'.$this->parsed['pass'] : '').'@' : '')
            .((isset($this->parsed['host'])) ? $this->parsed['host'] : '')
            .((isset($this->parsed['port'])) ? ':'.$this->parsed['port'] : '')
            .((isset($this->parsed['path'])) ? $this->parsed['path'] : '')
            .((isset($this->parsed['query'])) ? '?'.$this->parsed['query'] : '')
            .((isset($this->parsed['fragment'])) ? '#'.$this->parsed['fragment'] : '');
    }

    /**
     * Construct a full url with the parsed url elements
     * resulted from a parse_url() function call.
     *
     * @return string
     */
    private function reassembleWithoutRoot()
    {
        if (!$this->root) {
            return $this->reassemble();
        }

        /**
         * In some rare conditions the path in interpreted as the host when there is no domain.tld format given.
         * This is still considered a valid url, be it with only a tld as indication.
         */
        $path = (isset($this->parsed['path']) && $this->parsed['path'] != $this->root->host())
                    ? $this->parsed['path']
                    : '';

        return $path
            .((isset($this->parsed['query'])) ? '?'.$this->parsed['query'] : '')
            .((isset($this->parsed['fragment'])) ? '#'.$this->parsed['fragment'] : '');
    }
}
