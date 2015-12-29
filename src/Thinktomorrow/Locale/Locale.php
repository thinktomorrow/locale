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

    /**
     * Set locale according to following priority:
     *
     * 0) If locale is passed as parameter, this locale will be forced
     * 1) If locale is in request as query parameter e.g. ?lang=fr,
     * 2) If locale is in request url (subdomain, domain or segment) eg. nl.example.com, example.nl or example.com/nl
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

            if($this->validateLocale($this->request->cookie('locale')))
            {
                $locale = $this->request->cookie('locale');
            }

            if($this->isLocaleInUrl())
            {
                $locale = $this->getLocaleFromUrl();
            }

            if($this->validateLocale($this->request->get('lang')))
            {
                $locale = $this->request->get('lang');
            }
        }

        app()->setlocale($locale);

        return $locale;
    }

    /**
     * Get the current locale - Fail if passed locale is invalid
     *
     * @param null $locale
     * @return null|string
     */
    public function getOrFail($locale = null)
    {
        return $this->get($locale,true);
    }

    private function getLocaleFromUrl()
    {
        if($locale = $this->getTldLocale()) return $locale;
        if($locale = $this->getSubdomainLocale()) return $locale;
        if($locale = $this->getLocaleSegment()) return $locale;

        return false;
    }

    public function isLocaleInUrl()
    {
        if($this->getTldLocale()) return true;
        if($this->getSubdomainLocale()) return true;
        if($this->getLocaleSegment()) return true;

        return false;
    }

    private function getLocaleSegment()
    {
        $segment = $this->request->segment(1);

        return ($this->validateLocale($segment)) ? $segment : false;
    }

    private function getTldLocale()
    {
        $host = explode('.',$this->request->getHost());

        $tld = last($host);

        return ($this->validateLocale($tld)) ? $tld : false;
    }

    private function getSubdomainLocale()
    {
        $host = explode('.',$this->request->getHost());

        if(count($host)>2)
        {
            $subdomain = $host[count($host)-2];

            return ($this->validateLocale($subdomain)) ? $subdomain : false;
        }

        return false;
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

}
