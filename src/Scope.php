<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Values\Locale;
use Thinktomorrow\Locale\Values\Root;

class Scope
{
    private array $locales;
    private static ?Locale $activeLocale = null;

    /**
     * The default locale. In the request path
     * it's the hidden segment, e.g. /.
     */
    private Locale $default;

    /**
     * When the canonical scope has a root set to be
     * other than the current, that specific root is defined here
     * By default the current request root is of use (NULL).
     */
    private ?Root $customRoot = null;

    public function __construct(array $locales)
    {
        $defaultLocale = ! isset($locales['/']) ? null : $locales['/'];

        // When no default locale is set, we take the last locale as default.
        if (! $defaultLocale) {
            if(count($locales) < 1) {
                throw new InvalidScope('Default locale is required for scope. Add this as \'/\' => locale.');
            }

            $defaultLocale = $locales[array_key_last($locales)];
        }

        $this->locales = $locales;
        $this->default = Locale::from($defaultLocale);
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
        return self::$activeLocale;
    }

    public static function setActiveLocale(Locale $locale): void
    {
        self::$activeLocale = $locale;
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
            $locale = $this->activeLocale() ? $this->activeLocale()->get() : $this->defaultLocale()->get();
        }

        return ($key = array_search($locale, $this->locales)) ? $key : null;
    }

    public function activeSegment(): ?string
    {
        return $this->segment($this->activeLocale());
    }

    public function validateLocale(string $locale = null): bool
    {
        if (! $locale) {
            return false;
        }

        return in_array($locale, $this->locales);
    }

    public function validateSegment(string $segment = null): bool
    {
        if (! $segment) {
            return false;
        }

        return isset($this->locales[$segment]);
    }
}
