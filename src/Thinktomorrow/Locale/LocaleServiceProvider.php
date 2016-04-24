<?php namespace Thinktomorrow\Locale;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class LocaleServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->publishes([
            __DIR__.'/config/locale.php' => config_path('thinktomorrow/locale.php')
        ]);

        $this->app->bind(Locale::class,function($app){
            return new Locale($app['request'],$this->getConfig());
        });

        $this->app->bind(LocaleUrl::class,function($app){
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Locale'],
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