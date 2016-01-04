<?php

return [

    /**
     * Enable the localization of the application routes and
     * listening to locales on the url. The locale will
     * always be saved in cookie.
     */
    'enable' => true,

    'available_locales'     => ['en'],

    /**
     * Fallback locale
     *
     * If null the default Laravel fallback will be used
     */
    'fallback_locale'       => null,

    /**
     * Fallback route name
     *
     * After a language switch (via form), if possible, the user is
     * directed back to his current page. If this previous route
     * lacks a routename, the following fallback will be used.
     *
     * Provide a decent fallback as routename.
     */
    'fallback_routename'       => 'home',

    /**
     * Localise our routes by url segment.
     * This will allow the route creations to inject the current used locale
     * Disable the feature by setting this value to null.
     *
     * e.g. /nl/example
     */
    'locale_slug'    => 'locale_slug',

    /**
     * Naked locale
     *
     * Provide the default locale for non-localized url endpoints. This displays the content
     * in this given locale without the presence of a locale in the url. e.g. example.com
     * gives the nl content instead of example.com/nl. Null means this feature is off
     *
     */
    'naked_locale'       => null,

    /**
     * List of all locales and their specifics
     *
     */
    'locales'   => [

        'nl'        => '',

    ],

];