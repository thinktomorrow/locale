<?php

namespace Thinktomorrow\Locale\Services;

use Thinktomorrow\Locale\Exceptions\InvalidConfig;

class Config implements \ArrayAccess
{
    /**
     * @var array
     */
    private $config;

    private function __construct(array $config)
    {
        $this->validate($config);

        $this->config = $this->sanitize($config);
    }

    public static function from(array $config)
    {
        return new static($config);
    }

    public function get($key)
    {
        if(!isset($this->config[$key])) throw new \InvalidArgumentException('No config value found by key ['.$key.']');

        return $this->config[$key];
    }

    public function all(): array
    {
        return $this->config;
    }

    private function sanitize(array $config): array
    {
        $locales = $config['locales'];
        $locales = $this->convertSingleEntryToDefault($locales);
        $locales = $this->removeSlashes($locales);

        $config['locales'] = $locales;

        return $config;
    }

    /**
     * @param array $locales
     * @return array
     */
    private function removeSlashes(array $locales): array
    {
        foreach($locales as $group => $segments)
        {
            foreach ($segments as $segment => $locale) {
                // remove slashes if any
                if ($segment != '/' && false !== strpos($segment, '/')) {
                    $_segment = str_replace('/', '', $segment);

                    unset($locales[$group][$segment]);
                    $locales[$group][$_segment] = $locale;
                }
            }
        }

        return $locales;
    }

    /**
     * @param array $locales
     * @return array
     */
    private function convertSingleEntryToDefault(array $locales): array
    {
        foreach ($locales as $group => $segments) {
            // If single locale is passed, it's automatically the default for this group
            if (!is_array($segments)) {
                $locales[$group] = $segments = ['/' => $segments];
            }
        }

        return $locales;
    }

    /**
     * @param array $config
     */
    private function validate(array $config)
    {
        if (!isset($config['locales'])) {
            throw new InvalidConfig('Value [Locales] is missing for config structure.');
        }

        $locales = $config['locales'];

        if (!isset($locales['default'])) {
            throw new InvalidConfig('Group [default] is missing for locales structure.');
        }

        if (is_array($locales['default']) && !isset($locales['default']['/'])) {
            throw new InvalidConfig('Group [default] is missing the default locale. e.g. ["/" => "en"]');
        }

        foreach ($locales as $group => $segments) {
            if (!is_string($group)) {
                throw new InvalidConfig('Invalid config structure for locales group [' . $group . ']');
            }
        }
    }

    public function offsetExists($offset)
    {
        if(!is_string($offset) && !is_int($offset)) return false;
        return array_key_exists($offset, $this->config);
    }
    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }
    public function offsetSet($offset, $value)
    {
        if(is_null($offset)) $this->config[] = $value;
        else $this->config[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function toArray(): array
    {
        return $this->config;
    }
}