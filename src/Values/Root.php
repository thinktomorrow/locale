<?php
declare(strict_types=1);

namespace Thinktomorrow\Locale\Values;

use Thinktomorrow\Locale\Exceptions\InvalidUrl;

class Root
{
    private bool $valid = false;

    private ?string $scheme = null;
    private ?string $host = null;
    private ?string $port = null;

    private bool $schemeless = false;
    private string $defaultScheme = 'http';
    private bool $secure = false;

    private function __construct(string $host)
    {
        $this->parse($host);

        if (false !== filter_var($this->get(), FILTER_VALIDATE_URL)) {
            $this->valid = true;
        }
    }

    public static function fromString(string $host): self
    {
        return new self($host);
    }

    public function get(): string
    {
        $scheme = (! is_null($this->scheme)) ? $this->scheme.'://' : ($this->schemeless ? '//' : $this->defaultScheme.'://');
        $port = (! is_null($this->port)) ? ':'.$this->port : null;

        if ($scheme == 'http://' && $this->secure) {
            $scheme = 'https://';
        }

        return $scheme.$this->host.$port;
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    public function secure(): self
    {
        $this->secure = true;
        $this->scheme = 'https';

        return $this;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function scheme(): ?string
    {
        return $this->scheme;
    }

    private function parse(string $host)
    {
        // Sanitize url input a bit to remove double slashes, but do not remove first slashes
        if ($host == '//') {
            $host = '/';
        }

        $parsed = parse_url($host);

        if (false === $parsed) {
            throw new InvalidUrl('Failed to parse url. Invalid url ['.$host.'] passed as parameter.');
        }

        // If a schemeless url is passed, parse_url will ignore this and strip the first tags,
        // so we keep a reminder to explicitly reassemble the 'anonymous scheme' manually
        $this->schemeless = ! isset($parsed['scheme']) && (str_starts_with($host, '//') && isset($parsed['host']));

        $this->scheme = $parsed['scheme'] ?? null;
        if ($this->scheme == 'https') {
            $this->secure();
        }

        $this->host = $this->parseHost($parsed);
        $this->port = isset($parsed['port']) ? (string) $parsed['port'] : null;
    }

    public function __toString(): string
    {
        return $this->get();
    }

    private function parseHost(array $parsed): string
    {
        if (isset($parsed['host'])) {
            return $parsed['host'];
        }

        if (! isset($parsed['path'])) {
            return '';
        }

        return (0 < strpos($parsed['path'], '/'))
                    ? substr($parsed['path'], 0, strpos($parsed['path'], '/'))
                    : $parsed['path'];
    }
}
