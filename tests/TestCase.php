<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\LocaleServiceProvider;
use Thinktomorrow\Locale\LocaleUrl;
use Thinktomorrow\Locale\Values\Config;

class TestCase extends OrchestraTestCase
{
    protected $localeUrl;

    protected function getPackageProviders($app)
    {
        return [LocaleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.lang'] = $this->getStubDirectory('lang');
        $app['config']->set('app.locale', 'nl');
        $app['config']->set('app.fallback_locale', 'nl');

        // Dimsav package dependency requires us to set the fallback locale via this config
        // It should if config not set be using the default laravel fallback imo
        $app['config']->set('translatable.fallback_locale', 'en');
    }

    private function getStubDirectory($dir = null)
    {
        return __DIR__.'/stubs/'.$dir;
    }

    protected function refreshBindings($defaultLocale = 'nl', $domain = null)
    {
        if($domain)
        {
            // Force root url for testing
            app(UrlGenerator::class)->forceRootUrl($domain);
        }

        $config = Config::from([
            'locales' => [
                'example.com' => [
                    'de' => 'DE_de',
                    'fr' => 'BE_fr',
                    '/' => 'FR_fr',
                ],
                '*.foobar.com' => [
                    'be-fr' => 'BE_fr',
                    '/' => 'FR_fr',
                ],
                '*' => [
                    'nl' => 'BE-nl',
                    'en' => 'en-gb',
                    '/' => $defaultLocale,
                ]
            ],
            'canonicals' => [
                'FR_fr' => 'fr.foobar.com',
                'BE-nl' => 'https://www.foobar.com',
                'be-de' => 'https://german-foobar.de',
            ],
            'route_key' => 'locale_slug',
        ]);

        app()->singleton('Thinktomorrow\Locale\Detect', function ($app) use ($config){
            return new Detect($app['request'], $config );
        });

        app()->singleton('Thinktomorrow\Locale\LocaleUrl', function ($app) use($config) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Thinktomorrow\Locale\Parsers\RouteParser'],
                $config
            );
        });

        $this->localeUrl = app(LocaleUrl::class);

        app()->setLocale($defaultLocale);
    }
}
