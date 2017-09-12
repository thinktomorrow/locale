<?php

namespace Thinktomorrow\Locale;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;

class LocaleServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once __DIR__ . '/helpers.php';

        $this->publishes([
            __DIR__ . '/config/locale.php' => config_path('thinktomorrow/locale.php'),
        ]);

        $this->app->singleton(Detect::class, function ($app) {
            return new Detect($app['request'], $this->getConfig());
        });

        $this->app->singleton(UrlParser::class, function ($app) {
            return new UrlParser(
                $app['Thinktomorrow\Locale\Detect'],
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
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Thinktomorrow\Locale\Parsers\RouteParser'],
                $this->getConfig()
            );
        });

        $this->app->alias(Detect::class, 'tt-locale');
        $this->app->alias(LocaleUrl::class, 'tt-locale-url');
    }

    private function getConfig()
    {
        if (file_exists(config_path('thinktomorrow/locale.php'))) {
            return require config_path('thinktomorrow/locale.php');
        }

        return require __DIR__ . '/config/locale.php';
    }
}
