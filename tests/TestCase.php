<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\UrlGenerator;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\LocaleUrl;
use Thinktomorrow\Locale\Values\Config;

class TestCase extends OrchestraTestCase
{
    protected $localeUrl;

    protected function getPackageProviders($app)
    {
        return [\Thinktomorrow\Locale\LocaleServiceProvider::class];
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

    protected function refreshBindings($defaultLocale = 'nl')
    {
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
                'BE-nl' => 'https://www.foobar.com'
            ],
            'placeholder' => 'locale_slug',
        ]);

        app()->singleton('Thinktomorrow\Locale\Detect', function ($app) use ($config){
            return new Detect($app['request'], $config );
        });

        app()->singleton('Thinktomorrow\Locale\LocaleUrl', function ($app) use($config) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParserContract'],
                $app['Thinktomorrow\Locale\Parsers\RouteParserContract'],
                $config
            );
        });

        $this->localeUrl = app(LocaleUrl::class);

        app()->setLocale($defaultLocale);
    }

    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $driver
     *
     * @return void
     */
    public function be(Authenticatable $user, $driver = null)
    {
        // TODO: Implement be() method.
    }
}
