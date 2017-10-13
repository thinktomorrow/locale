<?php

namespace Thinktomorrow\Locale\Services;

use Thinktomorrow\Locale\Exceptions\InvalidScope;

class Scope
{
    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $root;

    /**
     * The default locale. In the request path
     * it's the hidden segment, e.g. /
     *
     * @var Locale
     */
    private $default;

    public function __construct(array $locales, Root $root)
    {
        if(!isset($locales['/'])) throw new InvalidScope('Default locale is required for scope.');

        $this->locales = $locales;
        $this->default = Locale::from($this->locales['/']);
        $this->root = $root;
    }

    /**
     * Get the locale by key (segment)
     *
     * @param $key
     * @return null|Locale
     */
    public function get($key): ?Locale
    {
        return isset($this->locales[$key]) ? Locale::from($this->locales[$key]) : null;
    }

    /**
     * Get the url segment which corresponds with the passed locale
     *
     * @param $locale
     * @return null|string
     */
    public function segment($locale): ?string
    {
        return ($key = array_search($locale, $this->locales)) ? $key : null;
    }

    public function activeSegment(): ?string
    {
        return $this->segment(app()->getLocale());
    }

    public function all(): array
    {
        return $this->locales;
    }

    public function validate(string $locale = null): bool
    {
        if(!$locale) return false;

        return (in_array($locale, $this->locales));
    }

    public function validateSegment(string $segment = null): bool
    {
        if(!$segment) return false;

        return isset($this->locales[$segment]);
    }

    public function default(): Locale
    {
        return $this->default;
    }

    public function root(): Root
    {
        return $this->root;
    }
}