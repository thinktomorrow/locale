<?php

namespace Thinktomorrow\Locale;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;
use Thinktomorrow\Locale\Values\Config;

class LocaleServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once __DIR__ . '/helpers.php';

        $this->publishes([
            __DIR__ . '/config/locale.php' => config_path('thinktomorrow/locale.php'),
        ]);

        $this->app->singleton(DetectLocaleAndScope::class, function ($app) {
            return new DetectLocaleAndScope($app['request'], Config::from($this->getConfigValues()));
        });

        $this->app->singleton(UrlParser::class, function ($app) {
            return new UrlParser(
                $app['Illuminate\Contracts\Routing\UrlGenerator']
            );
        });

        $this->app->singleton(RouteParser::class, function ($app) {
            return new RouteParser(
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['translator']
            );
        });

        $this->app->singleton(LocaleUrl::class, function ($app) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\DetectLocaleAndScope'],
                new UrlParser(
                    $app['Illuminate\Contracts\Routing\UrlGenerator']
                ),
                new RouteParser(
                    new UrlParser(
                        $app['Illuminate\Contracts\Routing\UrlGenerator']
                    ),
                    $app['translator']),
                $app['Thinktomorrow\Locale\Parsers\RouteParser']
            );
        });

        /**
         * Facade for getting current active scope
         */
        $this->app->singleton('tt-locale-scope', function ($app) {
            return $app->make(DetectLocaleAndScope::class)->detect()->getScope();
        });
    }

    private function getConfigValues()
    {
        if (file_exists(config_path('thinktomorrow/locale.php'))) {
            return require config_path('thinktomorrow/locale.php');
        }

        return require __DIR__ . '/config/locale.php';
    }
}
