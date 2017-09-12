<?php

namespace Thinktomorrow\Locale\Services;

final class ScopeHub
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

    public function findByHost(string $host): Scope
    {
        // If scheme is not passed, parse_url gives the host as the path...
        $parsed = parse_url($host);
        $host = $parsed['host'] ?? $parsed['path'];

        return $this->findByKey($this->findKey($host));
    }

    private function findKey(string $value): string
    {
        foreach($this->config->get('locales') as $scope => $locales)
        {
            $pattern = preg_quote($scope,'#');

            /**
             * The host pattern allows for an asterix which stands for a
             * wildcard of characters when matching the scope keys
             */
            if(false !== strpos($pattern, '*'))
            {
                $pattern = str_replace('\*','(.+)',$pattern);
            }

            if( preg_match("#^$pattern$#", $value) )
            {
                return $scope;
            }
        }

        return 'default';
    }

    // We limit the available locales to the current domain space, including the default ones
    private function findByKey(string $scopeKey): Scope
    {
        if($scopeKey && isset($this->config['locales'][$scopeKey]))
        {
            $locales = $this->config->get('locales');
            $locales = array_merge($locales['default'], $locales[$scopeKey]);

            return new Scope($locales);
        }

        return new Scope($this->config['locales']['default']);
    }
}