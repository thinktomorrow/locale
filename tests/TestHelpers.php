<?php

namespace Thinktomorrow\Locale\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Thinktomorrow\Locale\Detect;
use Thinktomorrow\Locale\Values\Root;
use Thinktomorrow\Locale\Values\Config;

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
        return Config::from(array_merge_recursive($overrides, [
            'locales' => [
                'example.com' => [
                    'segment-one' => 'locale-one',
                    'segment-two' => 'locale-two',
                    '/' => 'locale-three',
                ],
                '*' => [
                    'segment-four' => 'locale-four',
                    'segment-five' => 'locale-five',
                    '/' => 'locale-zero',
                ]
            ],
            'canonicals' => [
                //
            ],
            'secure' => false,
            'route_key' => 'locale_slug',
            'query_key' => 'locale',
        ]));
    }
}