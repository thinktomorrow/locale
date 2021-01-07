<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Values\Config;
use Thinktomorrow\Locale\Values\Root;

final class ScopeCollection
{
    /**
     * @var Config
     */
    private $config;

    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    public static function fromArray(array $config)
    {
        return new static(Config::from($config));
    }

    public static function fromConfig(Config $config)
    {
        return new static($config);
    }

    public function findByRoot(string $root): Scope
    {
        return $this->findByKey($this->guessKeyFromRoot($root));
    }

    /**
     * @param string $locale
     *
     * @return null|Scope
     */
    public function findCanonical(string $locale): ?Scope
    {
        $canonicals = $this->config->get('canonicals');

        if (! isset($canonicals[$locale])) {
            return null;
        }

        $scope = $this->findByRoot($canonicals[$locale]);

        return $scope->setCustomRoot(Root::fromString($canonicals[$locale]));
    }

    private function guessKeyFromRoot(string $value): string
    {
        foreach (array_keys($this->config->get('locales', [])) as $scopeKey) {
            $pattern = preg_quote($scopeKey, '#');

            /*
             * The host pattern allows for an asterix which stands for a
             * wildcard of characters when matching the scope keys.
             * The default '*' scope will match anything
             */
            if (false !== strpos($pattern, '*')) {
                $pattern = str_replace('\*', '(.+)', $pattern);
            }

            if (preg_match("#^(https?://)?(www\.)?$pattern/?$#", $value)) {
                return $scopeKey;
            }
        }

        // In case value remains empty, we return the default routing.
        return '*';
    }

    // We limit the available locales to the current domain space, including the default ones
    private function findByKey(string $scopeKey): Scope
    {
        $locales = $this->config->get('locales');

        $locales = array_merge($locales['*'], $locales[$scopeKey]);

        // We flip our values so in case of duplicate locales, the default one
        // is omitted and the one from the specific scope is preserved.
        return new Scope(array_flip(array_flip($locales)));
    }
}
