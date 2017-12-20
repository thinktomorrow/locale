<?php

namespace Thinktomorrow\Locale\Scopes;

use Thinktomorrow\Locale\Exceptions\InvalidScope;
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

    public function findByRoot(string $root, $asCanonical = false): Scope
    {
        return $this->findByKey(
            $this->findKey($root),
            ($asCanonical) ? Root::fromString($root) : null
        );
    }

    /**
     * @param string $locale
     * @return null|CanonicalScope
     */
    public function findCanonical(string $locale): ?CanonicalScope
    {
        $canonicals = $this->config->get('canonicals');

        if(!isset($canonicals[$locale])) return null;

        return $this->findByRoot($canonicals[$locale], true);
    }

    private function findKey(string $value): string
    {
        foreach($this->config->get('locales') as $scopeKey => $locales)
        {
            $pattern = preg_quote($scopeKey,'#');

            /**
             * The host pattern allows for an asterix which stands for a
             * wildcard of characters when matching the scope keys.
             * The default '*' scope will match anything
             */
            if(false !== strpos($pattern, '*'))
            {
                $pattern = str_replace('\*','(.+)',$pattern);
            }

//            if( preg_match("#^$pattern$#", $value) )
//            ^(https?:\/\/)?(www\.)?example\.com\/?$
            if( preg_match("#^(https?://)?(www\.)?$pattern/?$#", $value) )
            {
                return $scopeKey;
            }
        }

        // In case value remains empty, we return the default routing.
        return '*';
    }

    // We limit the available locales to the current domain space, including the default ones
    private function findByKey(string $scopeKey, Root $canonical = null): Scope
    {
        if(!$scopeKey || !isset($this->config['locales'][$scopeKey]))
        {
            throw new InvalidScope('Scope key [' . $scopeKey . '] does not exist.');
        }

        $locales = $this->config->get('locales');
        $locales = array_merge($locales['*'], $locales[$scopeKey]);

        return $canonical ? (new CanonicalScope($locales))->setRoot($canonical) : new Scope($locales);
    }
}