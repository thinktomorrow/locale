<?php

return [

    'available_locales'     => ['nl','fr','en'],

    /**
     * Fallback locale
     *
     * If null the default Laravel fallback will be used
     */
    'fallback_locale'       => 'nl',

    /**
     * Localise our routes by url segment.
     * This will allow the route creations to inject the current used locale
     * Disable the feature by setting this value to null.
     *
     * e.g. /nl/example
     */
    'locale_segment'    => 'locale_slug',

];