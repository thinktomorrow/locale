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
        require_once __DIR__.'/helpers.php';

        $this->publishes([
            __DIR__.'/config/locale.php' => config_path('thinktomorrow/locale.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/config/locale.php', 'thinktomorrow.locale'
        );

        $config = Config::from($this->getConfigValues());

        $this->app->singleton(Detect::class, function ($app) use($config) {
            return new Detect($app['request'], $config);
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

        $this->app->singleton(LocaleUrl::class, function ($app) use($config) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Thinktomorrow\Locale\Parsers\RouteParser'],
                $config
            );
        });

        /*
         * Facade for getting current active scope
         */
        $this->app->singleton('tt-locale-scope', function ($app) {
            return $app->make(Detect::class)->detectLocale()->getScope();
        });
    }

    private function getConfigValues()
    {
        if (file_exists(config_path('thinktomorrow/locale.php'))) {
            return require config_path('thinktomorrow/locale.php');
        }

        return require __DIR__.'/config/locale.php';
    }
}
