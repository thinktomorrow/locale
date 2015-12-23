<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Locale
{
    /**
     * @var Request
     */
    private $request;
    private $available_locales;
    private $fallback_locale;

    public function __construct(Request $request,$config)
    {
        $this->request = $request;

        $this->available_locales = $config['available_locales'];
        $this->fallback_locale = $config['fallback_locale'] ?: config('app.fallback_locale');
    }

    /**
     * Get the current locale
     *
     * @param null $locale
     * @param bool $strict - if locale is not found we abort
     * @return null|string
     */
    public function get($locale = null,$strict = false)
    {
        if($this->validateLocale($locale))
        {
            return $locale;
        }

        if($strict and (!$locale or !in_array($locale,$this->available_locales)) ) return false;

        return app()->getLocale();
    }

    public function getOrFail($locale = null)
    {
        return $this->get($locale,true);
    }

    /**
     * Generate a localized url for the application.
     *
     * @param  string $path
     * @param null $locale
     * @return string
     */
    public function localeUrl($url, $locale = null)
    {
        $locale = $this->get($locale);

        if(!$url) return $locale;

        // Check if already a full valid url
        if($this->validateUrl($url))
        {
            if(!$this->shouldUrlBeIgnored($url) and $this->validateLocale($locale)) $url = $this->injectLocaleInUrl($url,$locale);
        }
        else
        {
            // let /example turn into /nl/example
            $url = $locale.'/'.ltrim($url,'/');

            $url = $this->injectLocaleInUrl($url,$locale);
        }

        return $url;
    }

    /**
     * Set locale according to following priority:
     *
     * 1) If locale is in request (url segment /fr/home), this locale will be forced
     * 2) If locale is in request (url query param e.g. ?lang=fr),
     * 3) Default: get locale from cookie
     * 4) Otherwise: set locale to our fallback language
     *
     * @param null $locale
     * @return array|mixed|null|string
     */
    public function set($locale = null)
    {
        if(!$locale or !in_array($locale,$this->available_locales))
        {
            $locale = $this->fallback_locale;
        }

        if($this->validateLocale($this->request->cookie('locale')))
        {
            $locale = $this->request->cookie('locale');
        }

        if($this->validateLocale($this->request->get('lang')))
        {
            $locale = $this->request->get('lang');
        }

        if($this->validateLocale($this->request->segment(1)))
        {
            $locale = $this->request->segment(1);
        }

        app()->setlocale($locale);

        return $locale;
    }

    /**
     * @param null $locale
     * @return bool
     */
    private function validateLocale($locale = null)
    {
        if(!$locale) return false;

        return in_array($locale,$this->available_locales);
    }

    /**
     * @param null $url
     * @param $locale
     * @return mixed
     */
    private function injectLocaleInUrl($url,$locale = null)
    {
        $locale = $this->get($locale);

        $path = $original_path = trim(parse_url($url,PHP_URL_PATH),'/');

        // Should there already be a language segment present in the path url, we will remove it first
        if(!$path)
        {
            return rtrim($url,'/').'/'.$locale;
        }

        $first_segment = substr($path,0,strpos($path,'/'));
        if($this->validateLocale($first_segment)) $path = substr($path,strlen($first_segment)+1);

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
