<?php

namespace Thinktomorrow\Locale;

use Illuminate\Support\Str;

/**
 * Class LocaleUrl
 *
 * @deprecated out the scope of our package
 * @package Thinktomorrow\Locale
 */
class LocaleUrl
{
    /**
     * Generate a localized url for the application.
     *
     * @param $url
     * @param $locale
     * @return string
     */
    public function convert($url,$locale)
    {
        // Check if already a full valid url
        if($this->validateUrl($url))
        {
            if(!$this->shouldUrlBeIgnored($url)) $url = $this->injectLocaleInUrl($url,$locale);
        }
        else
        {
            // let /example turn into /nl/example
            //$url = $locale.'/'.ltrim($url,'/');

            $url = $this->injectLocaleInUrl($url,$locale);
        }

        return $url;
    }

    /**
     * @param null $url
     * @param $locale
     * @return mixed
     */
    private function injectLocaleInUrl($url,$locale)
    {
        $path = $original_path = trim(parse_url($url,PHP_URL_PATH),'/');

        // Should there already be a language segment present in the path url, we will remove it first
        if(!$path)
        {
            return rtrim($url,'/').'/'.$locale;
        }

        $first_segment = substr($path,0,strpos($path,'/'));
        //if($this->validateLocale($first_segment)) $path = substr($path,strlen($first_segment)+1);

        return str_replace($original_path, $locale . '/' . $path,$url);
    }

    /**
     * @param $path
     * @return bool
     */
    private function validateUrl($path)
    {
        if (Str::startsWith($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://']))
        {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param $path
     * @return bool
     */
    private function shouldUrlBeIgnored($path)
    {
        return (Str::startsWith($path, ['mailto:', 'tel:']));
    }
}