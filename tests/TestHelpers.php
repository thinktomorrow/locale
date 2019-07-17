<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Http\Request;
use Thinktomorrow\Locale\Scope;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\Values\Config;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Config as LaravelConfig;

trait TestHelpers
{
    protected function detectLocaleAfterVisiting($url, array $overrides = []): string
    {
        $root = Root::fromString($url)->get();
        // We enforce the environment to let the application believe
        // the root being visited is the current active root.
        putenv('APP_URL='.$root);
        
        // Reboot the application to accept this root
        $this->createApplication();
        $this->refreshLocaleBindings($overrides);

        app()->instance('request', Request::create($url, 'GET', [], [], [], $_SERVER));
        app(UrlGenerator::class)->forceRootUrl($root);

        return app(Detect::class)->getLocale();
    }

    protected function validConfig(array $overrides = [])
    {
        // Okay so we want to override all values but only the locales we want to prepend
        // This is because the order of the locales matters and we want to keep the
        // default as set below
        $overrides_locales = array_only($overrides, 'locales');
        if (!empty($overrides_locales)) {
            $overrides_locales = $overrides_locales['locales'];
        }

        $overrides_without_locales = array_except($overrides, 'locales');

        $locales = array_merge($overrides_locales, [
            'example.com' => [
                'segment-one' => 'locale-one',
                'segment-two' => 'locale-two',
                '/'           => 'locale-three',
            ],
            '*' => [
                'segment-four' => 'locale-four',
                'segment-five' => 'locale-five',
                '/'            => 'locale-zero',
            ],
        ]);

        return Config::from(array_merge([
            'locales'    => $locales,
            'canonicals' => [
                //
            ],
            'convert_locales'    => false,
            'convert_locales_to' => [

            ],
            'secure'          => false,
            'route_key'       => 'locale_slug',
            'query_key'       => 'locale',
            'routes_filename' => 'routes',
        ], $overrides_without_locales));
    }

    protected function getDefaultScope()
    {
        return new Scope([
            'segment-four' => 'locale-four',
            'segment-five' => 'locale-five',
            '/'            => 'locale-zero',
        ]);
    }
}
