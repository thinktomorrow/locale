<?php

namespace Thinktomorrow\Locale\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\Thinktomorrow\Locale\LocaleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.lang'] = $this->getStubDirectory('lang');
        $app['config']->set('app.locale','nl');
        $app['config']->set('app.fallback_locale','en');

        // Dimsav package dependency requires us to set the fallback locale via this config
        // It should if config not set be using the default laravel fallback imo
        $app['config']->set('translatable.fallback_locale','en');
    }
    private function getStubDirectory($dir = null)
    {
        return __DIR__.'/stubs/' . $dir;
    }
}