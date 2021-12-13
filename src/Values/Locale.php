<?php
declare(strict_types=1);

namespace Thinktomorrow\Locale\Values;

final class Locale
{
    /** Locale key identifier */
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $value): self
    {
        return new static($value);
    }

    public function withoutRegion(): self
    {
        $value = $this->value;

        if ($region = $this->region()) {
            $value = str_replace(['-'.$region, '_'.$region], '', $value);
        }

        return new static($value);
    }

    public function region(): ?string
    {
        $value = $this->value;

        if (str_contains($value, '-')) {
            return substr($value, strpos($value, '-') + 1);
        }

        if (str_contains($value, '_')) {
            return substr($value, strpos($value, '_') + 1);
        }

        return null;
    }

    public function get(): string
    {
        return $this->value;
    }

    public function equals($other): bool
    {
        return get_class($this) === get_class($other) && (string) $this === (string) $other;
    }

    public function __toString(): string
    {
        return $this->get();
    }
}
