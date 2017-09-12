<?php

namespace Thinktomorrow\Locale\Services;

use Thinktomorrow\Locale\Exceptions\InvalidScope;
use Thinktomorrow\Locale\Locale;

class Scope
{
    /**
     * @var array
     */
    private $locales;

    /**
     * The default locale. In the request path
     * it's the hidden segment, e.g. /
     *
     * @var Locale
     */
    private $default;

    public function __construct(array $locales)
    {
        if(!isset($locales['/'])) throw new InvalidScope('Default locale is required for scope.');

        $this->locales = $locales;
        $this->default = Locale::from($this->locales['/']);
    }

    public function get($key): ?Locale
    {
        return isset($this->locales[$key]) ? Locale::from($this->locales[$key]) : null;
    }

    public function all(): array
    {
        return $this->locales;
    }

    public function validate(Locale $locale): bool
    {
        return (in_array($locale->get(), $this->locales));
    }

    public function default(): Locale
    {
        return $this->default;
    }
}