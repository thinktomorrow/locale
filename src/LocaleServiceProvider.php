<?php

namespace Thinktomorrow\Locale;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\Locale\Parsers\RouteParserContract;
use Thinktomorrow\Locale\Parsers\UrlParserContract;
use Thinktomorrow\Locale\Values\Config;

class LocaleServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once __DIR__ . '/helpers.php';

        $this->publishes([
            __DIR__ . '/config/locale.php' => config_path('thinktomorrow/locale.php'),
        ]);

        $this->app->singleton(Detect::class, function ($app) {
            return new Detect($app['request'], Config::from($this->getConfigValues()));
        });

        $this->app->singleton(UrlParserContract::class, function ($app) {
            return new UrlParserContract(
                $app['Illuminate\Contracts\Routing\UrlGenerator']
            );
        });

        $this->app->singleton(RouteParserContract::class, function ($app) {
            return new RouteParserContract(
                $app['Thinktomorrow\Locale\Parsers\UrlParserContract'],
                $app['translator']
            );
        });

        $this->app->singleton(LocaleUrl::class, function ($app) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                new UrlParserContract(
                    $app['Illuminate\Contracts\Routing\UrlGenerator']
                ),
                new RouteParserContract(
                    new UrlParserContract(
                        $app['Illuminate\Contracts\Routing\UrlGenerator']
                    ),
                    $app['translator']),
                $app['Thinktomorrow\Locale\Parsers\RouteParserContract']
            );
        });

        $this->app->alias(Detect::class, 'tt-locale');
        $this->app->alias(LocaleUrl::class, 'tt-locale-url');
    }

    private function getConfigValues()
    {
        if (file_exists(config_path('thinktomorrow/locale.php'))) {
            return require config_path('thinktomorrow/locale.php');
        }

        return require __DIR__ . '/config/locale.php';
    }
}
