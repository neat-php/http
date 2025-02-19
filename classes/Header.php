<?php

namespace Neat\Http;

/**
 * HTTP Header Field
 */
class Header
{
    private string $name;
    private array $value;

    public function __construct(string $name, string ...$value)
    {
        $this->name = $this->normalizeHeader($name);
        $this->value = $this->normalizeValues(...$value);
    }

    public function __toString(): string
    {
        return $this->line();
    }

    public function line(): string
    {
        return sprintf('%s: %s', $this->name, $this->value());
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return implode(',', $this->value);
    }

    /**
     * Applies Camel-Case to the header
     */
    private function normalizeHeader(string $header): string
    {
        return ucwords($header, '-');
    }

    /**
     * Removes unnecessary whitespace
     */
    private function normalizeValues(string ...$value): array
    {
        return array_map([$this, 'normalizeValue'], $value);
    }

    /**
     * Removes unnecessary whitespace
     */
    private function normalizeValue(string $value): string
    {
        return trim($value, " \t");
    }
}
