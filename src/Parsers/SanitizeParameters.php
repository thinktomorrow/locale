<?php

namespace Thinktomorrow\Locale\Parsers;

class SanitizeParameters
{
    /**
     * Loop over the parameters and make sure they are properly url encoded.
     *
     * @param array $parameters
     *
     * @return array
     */
    public static function rawurlencode(array $parameters): array
    {
        return array_map(function ($param) {
            // Null values are kept as is, so they'll get properly removed.
            if (null === $param) {
                return $param;
            }

            $param = rawurlencode($param);

            $param = str_replace('%2F', '/', $param);
            $param = str_replace('%3F', '?', $param);
            $param = str_replace('%3D', '=', $param);

            return $param;
        }, $parameters);
    }
}
