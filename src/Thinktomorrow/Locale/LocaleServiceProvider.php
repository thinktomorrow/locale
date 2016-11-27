<?php namespace Thinktomorrow\Locale;

use Illuminate\Support\ServiceProvider;
use Thinktomorrow\Locale\Services\UrlParser;

class LocaleServiceProvider extends ServiceProvider {

    public function register()
    {
        require_once __DIR__.'/helpers.php';

        $this->publishes([
            __DIR__.'/config/locale.php' => config_path('thinktomorrow/locale.php')
        ]);

        $this->app->singleton(Locale::class,function($app){
            return new Locale($app['request'],$this->getConfig());
        });

        $this->app->singleton(UrlParser::class,function($app){
            return new UrlParser($app['Thinktomorrow\Locale\Locale']);
        });

        $this->app->singleton(LocaleUrl::class,function($app){
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Locale'],
                $app['Thinktomorrow\Locale\Services\UrlParser'],
                $app['Illuminate\Contracts\Routing\UrlGenerator'],
                $this->getConfig()
            );
        });

        $this->app->alias(Locale::class, 'tt-locale');
        $this->app->alias(LocaleUrl::class, 'tt-locale-url');

    }

    private function getConfig()
    {
        if(file_exists(config_path('thinktomorrow/locale.php')))
        {
            return require config_path('thinktomorrow/locale.php');
        }

        return require __DIR__.'/config/locale.php';
    }

}