<?php

namespace Thinktomorrow\Locale\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\LocaleServiceProvider;
use Thinktomorrow\Locale\LocaleUrl;

class TestCase extends OrchestraTestCase
{
    use TestHelpers;

    protected $localeUrl;

    public function setUp()
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [LocaleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.lang'] = $this->getStubDirectory('lang');
        $app['config']->set('app.locale', 'locale-fallback');
        $app['config']->set('app.fallback_locale', 'locale-fallback');

        // Dimsav package dependency requires us to set the fallback locale via this config
        // It should if config not set be using the default laravel fallback imo
        $app['config']->set('translatable.fallback_locale', 'en');
    }

    private function getStubDirectory($dir = null)
    {
        return __DIR__.'/stubs/'.$dir;
    }

    protected function refreshLocaleBindings(array $overrides = [])
    {
        $config = $this->validConfig($overrides);

        app()->singleton(Detect::class, function ($app) use ($config) {
            return new Detect($app['request'], $config);
        });

        app()->singleton(LocaleUrl::class, function ($app) use ($config) {
            return new LocaleUrl(
                $app['Thinktomorrow\Locale\Detect'],
                $app['Thinktomorrow\Locale\Parsers\UrlParser'],
                $app['Thinktomorrow\Locale\Parsers\RouteParser'],
                $config
            );
        });

        $this->localeUrl = app(LocaleUrl::class);

        app()->setLocale('locale-fallback');
    }
}
