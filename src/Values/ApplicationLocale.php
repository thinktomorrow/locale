<?php

declare(strict_types = 1);

namespace Thinktomorrow\Locale\Values;

class ApplicationLocale
{
    /** @var Config */
    private $config;

    /** @var Locale */
    private $originalLocale;

    /** @var Locale */
    private $convertedgs
Locale;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function from(Locale $locale)
    {
        $this->originalLocale = $locale;
    }

    public function get(): Locale
    {
        return $this->originalLocale;
    }
}