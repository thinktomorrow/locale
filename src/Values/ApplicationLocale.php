<?php

declare(strict_types = 1);

namespace Thinktomorrow\Locale\Values;

use Illuminate\Support\Facades\Config as LaravelConfig;

class ApplicationLocale
{
    /** @var Config */
    private $config;

    /** @var Locale */
    private $originalLocale;

    /** @var Locale */
    private $locale;

    private function __construct(Locale $originalLocale, Config $config)
    {
        $this->originalLocale = $originalLocale;
        $this->config = $config;
    }

    public static function from($originalLocale)
    {
        if(is_string($originalLocale)){
            $originalLocale = Locale::from($originalLocale);
        }

        return new static($originalLocale, Config::from(app('config')->get('thinktomorrow.locale')));
    }

    public function get(): Locale
    {
        // Convert ...

        return $this->locale;
    }
}