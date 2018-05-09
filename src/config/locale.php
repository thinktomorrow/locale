<?php

return [

    /*
     * Available locale scopes for the application
     *
     * Matches are done from top to bottom so declare the more specific hosts above general ones.
     * The only mandatory scope entry is '*' which are the default locales and apply to all requests.
     */
    'locales' => [
        '*' => 'en',
    ],

    /*
     * In case your locales contains both language as region references and this locale differs
     * from the application's locale, you can opt to point this locale to the one as expected
     * by the application.
     *
     * The value can be either: auto, true or false.
     * - false      default value. Keeps the locale values as set as above.
     * - true       locales will be converted to the ones defined below in the 'convert_locales_to'
     * - auto       if the format is in the RFC 4646 format with hyphen (en-US) or underscore (en_US), we will automatically convert the locale
     *              to the language portion. e.g. en-US will be interpreted as 'en'.
     */
    'convert_locales' => false,

    'convert_locales_to' => [
        // e.g. 'nl-BE' => 'nl',
    ],

    /*
     * Define specific canonical domains here.
     *
     * Specify unique domains for each locale to avoid crawler duplicate content warnings.
     * By default the current scope is used to determine the canonical domain. Here
     * you can specify a domain for a certain locale if the same locale is present in multiple domains.
     */
    'canonicals' => [
        // e.g. 'nl' => 'http://example.nl',
    ],

    /*
     * If set to true, all urls created by localeroute() and localeurl() will be forced to https,
     * Domain defined in the canonicals setting will keep their scheme if it is provided.
     * If set to false, url schemes will remain unmodified.
     */
    'secure' => true,

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

    /*
     * language filename where the route names are stored
     */
    'routes_filename' => 'routes',

];
