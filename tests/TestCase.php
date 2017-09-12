<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\UrlGenerator;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\LocaleUrl;

class TestCase extends OrchestraTestCase
{
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

    protected function refreshBindings($defaultLocale = 'nl', $hiddenLocale = 'nl')
    {
        app()->singleton('Thinktomorrow\Locale\Detect', function ($app) use ($hiddenLocale) {
            return new Detect($app['request'], [
                'available_locales' => ['nl', 'fr', 'en'],
                'fallback_locale'   => null,
                'hidden_locale'     => $hiddenLocale,
            ]);
        });

        // Force root url for testing
        app(UrlGenerator::class)->forceRootUrl('http://example.com');

        app()->singleton('Thinktomorrow\Locale\LocaleUrl', function ($app) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Thinktomorrow\Locale\Parsers\RouteParser'],
                ['placeholder' => 'locale_slug']
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
