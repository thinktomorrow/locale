<?php

namespace Thinktomorrow\Locale\Services;

use Thinktomorrow\Locale\Exceptions\InvalidScope;

final class ScopeHub
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Root
     */
    private $root;

    private function __construct(Config $config, Root $root)
    {
        $this->config = $config;

        // Default root
        $this->root = $root;
    }

    public static function fromArray(array $config, Root $root)
    {
        return new static(Config::from($config), $root);
    }

    public static function fromConfig(Config $config, Root $root)
    {
        return new static($config, $root);
    }

    public function findByRoot(string $root): Scope
    {
        $root = Root::fromString($root);

        $scopeKey = $this->findKey($root->get());

        /**
         * If root is found and doesn't result in the default scope
         * We can safely assume this is the intended scoped root
         */
        if($scopeKey != '*' && $root->valid()) $this->root = $root;

        return $this->findByKey($scopeKey);
    }

    public function findByCanonical(Locale $locale): ?Scope
    {
        $canonicals = $this->config->get('canonicals');

        if(!isset($canonicals[$locale->get()])) return null;

        return $this->findByRoot($canonicals[$locale->get()]);
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
    private function findByKey(string $scopeKey): Scope
    {
        if(!$scopeKey || !isset($this->config['locales'][$scopeKey]))
        {
            throw new InvalidScope('Scope key [' . $scopeKey . '] does not exist.');
        }

        $locales = $this->config->get('locales');
        $locales = array_merge($locales['*'], $locales[$scopeKey]);

        return new Scope($locales, $this->root);
    }
}