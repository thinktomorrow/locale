<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Root;

class Scope
{
    /**
     * @var array
     */
    private $locales;

    /**
     * The active locale
     *
     * @var Locale
     */
    private static $activeLocale;

    /**
     * The default locale. In the request path
     * it's the hidden segment, e.g. /.
     *
     * @var Locale
     */
    private $default;

    /**
     * When the canonical scope has a root set to be
     * other than the current, that specific root is defined here
     * By default the current request root is of use (NULL).
     *
     * @var null|Root
     */
    private $customRoot = null;

    public function __construct(array $locales)
    {
        if (!isset($locales['/'])) {
            throw new InvalidScope('Default locale is required for scope. Add this as \'/\' => locale.');
        }
        $this->locales = $locales;
        $this->default = Locale::from($this->locales['/']);
    }

    public function setCustomRoot(Root $customRoot)
    {
        $this->customRoot = $customRoot;

        return $this;
    }

    public function customRoot(): ?Root
    {
        return $this->customRoot;
    }

    /**
     * Get the locale by segment identifier.
     *
     * @param $segment
     *
     * @return null|Locale
     */
    public function findLocale($segment): ?Locale
    {
        return isset($this->locales[$segment]) ? Locale::from($this->locales[$segment]) : null;
    }

    public function locales(): array
    {
        return $this->locales;
    }

    public function defaultLocale(): Locale
    {
        return $this->default;
    }

    public static function activeLocale(): ?Locale
    {
        return static::$activeLocale;
    }

    public static function setActiveLocale(Locale $locale)
    {
        static::$activeLocale = $locale;
    }

    /**
     * Get the url segment which corresponds with the passed locale.
     *
     * @param null $locale
     *
     * @return null|string
     */
    public function segment($locale = null): ?string
    {
        if (is_null($locale)) {
            return $this->activeSegment();
        }

        return ($key = array_search($locale, $this->locales)) ? $key : null;
    }

    public function activeSegment(): ?string
    {
        return $this->segment($this->activeLocale());
    }

    public function validateLocale(string $locale = null): bool
    {
        if (!$locale) {
            return false;
        }

        return in_array($locale, $this->locales);
    }

    public function validateSegment(string $segment = null): bool
    {
        if (!$segment) {
            return false;
        }

        return isset($this->locales[$segment]);
    }
}
