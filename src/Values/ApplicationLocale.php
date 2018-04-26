<?php

declare(strict_types=1);

namespace Thinktomorrow\Locale\Values;

class ApplicationLocale
{
    /** @var Config */
    private $config;

    /** @var Locale */
    private $originalLocale;

    private function __construct(Locale $originalLocale, Config $config)
    {
        $this->originalLocale = $originalLocale;
        $this->config = $config;
    }

    public static function from($originalLocale)
    {
        if (is_string($originalLocale)) {
            $originalLocale = Locale::from($originalLocale);
        }

        return new static($originalLocale, Config::from(app('config')->get('thinktomorrow.locale', [])));
    }

    /**
     * Convert locale to application locale.
     *
     * @return Locale
     */
    public function get(): Locale
    {
        $locale = $this->originalLocale;

        $convert_locales = $this->config->get('convert_locales');
        $conversions = $this->config->get('convert_locales_to', []);

        if ('auto' === $convert_locales) {
            $locale = isset($conversions[$locale->get()])
                ? Locale::from($conversions[$locale->get()])
                : $locale->withoutRegion();
        } elseif (true === $convert_locales && isset($conversions[$locale->get()])) {
            $locale = Locale::from($conversions[$locale->get()]);
        }

        return $locale;
    }

    public function equals(self $other): bool
    {
        return get_class($this) === get_class($other) && (string) $this === (string) $other;
    }

    public function __toString(): string
    {
        return $this->get()->get();
    }
}
