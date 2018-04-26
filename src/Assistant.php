<?php

namespace Thinktomorrow\Locale;

class Assistant
{
    public static function matchesRoot($host, $root): bool
    {
        $pattern = preg_quote($host, '#');

        /*
         * The host pattern allows for an asterix which stands for a
         * wildcard of characters when matching the scope keys.
         * The default '*' scope will match anything
         */
        if (false !== strpos($pattern, '*')) {
            $pattern = str_replace('\*', '(.+)', $pattern);
        }

        return preg_match("#^(https?://)?(www\.)?$pattern/?$#", $root);
    }
}