<?php

return [

    /*
     * Available locale scopes for the application
     *
     * Matches are done from top to bottom so declare the more specific hosts above general ones.
     * The only mandatory scope is '*' which are the default locales and apply to all requests.
     */
    'locales' => [
        'fr.example.com'   => [
            '/en' => 'en',
            '/'   => 'fr',
        ],
        'staging.example.be' => 'nl',
        'example.com'   => [
            '/fr' => 'fr',
            '/'   => 'en',
        ],
        'mijnkindishetallerliefste.be' => 'nl',
        'monenfantestleplusadorable.be' => [
            '/' => 'fr-BE',
        ],
        'monenfantestleplusadorable.fr' => 'fr',
        '*' => [
            'en' => 'en-GB',
            'us' => 'en-US',
            'fr' => 'fr',
            '/'  => 'nl',
        ],
    ],

    /*
     * Query parameter
     *
     * The locale can be passed as query parameter to pass a specific locale to the request.
     * This can be handy for ajax requests. By default this is set to 'locale'.
     */
    'query_key' => 'locale',

    /*
     * Route uri placeholder
     *
     * When this parameter key is passed, it will inject a
     * custom locale to the LocaleUrl::route() function
     * e.g. LocaleUrl::route('pages.home',['locale' => 'en']);
     */
    'route_key' => 'locale',

];
