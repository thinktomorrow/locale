<?php

return [

    /*
     * Available locale scopes for the application
     *
     * Matches are done from top to bottom so declare the more specific hosts above general ones.
     * The only mandatory scope entry is '*' which are the default locales and apply to all requests.
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

    /**
     * Define your canonical domains here.
     * Specify unique domains for each locale to avoid crawler duplicate content warnings.
     * By default the current scope is used to determine the canonical domain. Here
     * you can specify a domain for a certain locale if the same locale is present in multiple domains.
     */
    'canonicals' => [
        // e.g. 'nl' => 'http://example.nl',
    ],

    /*
     * Route uri placeholder
     *
     * When this parameter key is passed, it will inject a
     * custom locale to the LocaleUrl::route() function
     * e.g. LocaleUrl::route('pages.home',['locale' => 'en']);
     */
    'route_key' => 'locale',

    /*
     * Query parameter
     *
     * The locale can be passed as query parameter to pass a specific locale to the request.
     * This can be handy for ajax requests. By default this is set to 'locale'.
     */
    'query_key' => 'locale',

    /**
     * language filename where the route names are stored
     */
    'routes_filename' => 'routes',

];
