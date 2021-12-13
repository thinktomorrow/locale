<?php
declare(strict_types=1);

namespace Thinktomorrow\Locale\Values;

use ArrayAccess;
use Thinktomorrow\Locale\Exceptions\InvalidConfig;

class Config implements ArrayAccess
{
    private array $config;

    private function __construct(array $config)
    {
        $this->validate($config);

        $this->config = $this->sanitize($config);
    }

    public static function from(array $config): self
    {
        return new static($config);
    }

    public function get($key, $default = null)
    {
        if (! isset($this->config[$key])) {
            return $default;
        }

        return $this->config[$key];
    }

    public function all(): array
    {
        return $this->config;
    }

    private function sanitize(array $config): array
    {
        // Sanitize locales
        $locales = $config['locales'];
        $locales = $this->convertSingleEntryToDefault($locales);
        $locales = $this->removeSlashes($locales);
        $locales = $this->removeTrailingDomainSlashes($locales);
        $config['locales'] = $locales;

        // Compute canonicals for all locales
        $config['canonicals'] = $this->computeCanonicals($config);

        return $config;
    }

    private function computeCanonicals(array $config): array
    {
        $canonicals = $config['canonicals'] ?? [];

        foreach ($config['locales'] as $rootKey => $locales) {

            // wildcard domains are not accepted as canonicals as we cannot know to which root this should resolve to.
            if (str_contains($rootKey, '*')) {
                continue;
            }

            foreach ($locales as $locale) {
                if (! isset($canonicals[$locale])) {
                    $canonicals[$locale] = $rootKey;
                }
            }
        }

        return $canonicals;
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function removeSlashes(array $locales): array
    {
        foreach ($locales as $group => $segments) {
            foreach ($segments as $segment => $locale) {
                // remove slashes if any e.g. '/nl' will be sanitized to 'nl'
                if ($segment != '/' && str_contains($segment, '/')) {
                    $_segment = str_replace('/', '', $segment);

                    unset($locales[$group][$segment]);
                    $locales[$group][$_segment] = $locale;
                }
            }
        }

        return $locales;
    }

    /**
     * e.g. example.com/ will be sanitized to example.com.
     *
     * @param array $locales
     *
     * @return array
     */
    private function removeTrailingDomainSlashes(array $locales): array
    {
        foreach ($locales as $scopeKey => $segments) {
            unset($locales[$scopeKey]);
            $locales[rtrim($scopeKey, '/')] = $segments;
        }

        return $locales;
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function convertSingleEntryToDefault(array $locales): array
    {
        foreach ($locales as $group => $segments) {
            // If single locale is passed, it's considered the default for this group
            if (! is_array($segments)) {
                $locales[$group] = ['/' => $segments];
            }
        }

        return $locales;
    }

    /**
     * @param array $config
     */
    private function validate(array $config)
    {
        if (! isset($config['locales'])) {
            throw new InvalidConfig('Value [Locales] is missing for config structure.');
        }

        $locales = $config['locales'];

        if (! isset($locales['*'])) {
            throw new InvalidConfig('Default group [*] is missing for locales structure.');
        }

        if (is_array($locales['*']) && ! isset($locales['*']['/'])) {
            throw new InvalidConfig('Group [default] is missing the default locale. e.g. ["/" => "en"]');
        }

        foreach (array_keys($locales) as $group) {
            if (! is_string($group)) {
                throw new InvalidConfig('Invalid config structure for locales group ['.$group.']');
            }
        }

        $this->validateEachCanonicalLocaleExists($config);
    }

    /**
     * Each custom canonical entry should point to an existing locale.
     *
     * @param array $config
     */
    private function validateEachCanonicalLocaleExists(array $config)
    {
        $canonicals = $config['canonicals'] ?? [];
        foreach (array_keys($canonicals) as $locale) {
            if (! $this->existsAsLocale($config['locales'], $locale)) {
                throw new InvalidConfig('Canonical key '.$locale.' is not present as locale.');
            }
        }
    }

    private function existsAsLocale($existing_locales, $locale): bool
    {
        $flag = false;

        foreach ($existing_locales as $existing_locale) {
            if (is_array($existing_locale)) {
                if (true === $this->existsAsLocale($existing_locale, $locale)) {
                    $flag = true;

                    break;
                }
            }

            if ($existing_locale === $locale) {
                $flag = true;

                break;
            }
        }

        return $flag;
    }

    public function offsetExists($offset): bool
    {
        if (! is_string($offset) && ! is_int($offset)) {
            return false;
        }

        return array_key_exists($offset, $this->config);
    }

    public function offsetGet($offset): mixed
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->config[] = $value;
        } else {
            $this->config[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->config[$offset]);
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
