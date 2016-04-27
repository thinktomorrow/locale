<?php

namespace Thinktomorrow\Locale\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [\Thinktomorrow\Locale\LocaleServiceProvider::class];
    }
}