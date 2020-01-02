<?php

namespace Neat\Http;

/**
 * HTTP Header Field
 */
class Header
{
    /** @var string */
    private $name;

    /** @var string[] */
    private $value;

    /**
     * Header constructor
     *
     * @param string   $name
     * @param string[] $value
     */
    public function __construct(string $name, string ...$value)
    {
        $this->name  = $this->normalizeHeader($name);
        $this->value = $this->normalizeValues(...$value);
    }

    /**
     * Get header as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->line();
    }

    /**
     * Get header line
     *
     * @return string
     */
    public function line(): string
    {
        return sprintf('%s: %s', $this->name, $this->value());
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get header value
     *
     * @return string
     */
    public function value(): string
    {
        return implode(',', $this->value);
    }

    /**
     * Applies Camel-Case to the header
     *
     * @param string $header
     * @return string
     */
    private function normalizeHeader(string $header): string
    {
        return ucwords($header, '-');
    }

    /**
     * Removes unnecessary whitespace
     *
     * @param string ...$value
     * @return string[]
     */
    private function normalizeValues(string ...$value): array
    {
        return array_map([$this, 'normalizeValue'], $value);
    }

    /**
     * Removes unnecessary whitespace
     *
     * @param string $value
     * @return string
     */
    private function normalizeValue(string $value): string
    {
        return trim($value, " \t");
    }
}
