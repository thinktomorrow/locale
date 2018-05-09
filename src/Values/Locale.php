<?php

namespace Thinktomorrow\Locale\Values;

final class Locale
{
    /**
     * @var string Locale key identifier
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $value)
    {
        return new static($value);
    }

    public function withoutRegion()
    {
        $value = $this->value;

        if($region = $this->region()) {
            $value = str_replace(['-' . $region, '_' . $region], '', $value);
        }

        return new static($value);
    }

    public function region(): ?string
    {
        $value = $this->value;

        if (false !== strpos($value, '-')) {
            return substr($value, strpos($value, '-')+1);
        }

        if (false !== strpos($value, '_')) {
            return substr($value, strpos($value, '_')+1);
        }

        return null;
    }

    public function get(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return get_class($this) === get_class($other) && (string) $this === (string) $other;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
