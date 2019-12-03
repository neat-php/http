<?php declare(strict_types=1);

namespace Neat\Http;

/**
 * HTTP Header Field
 */
class Header
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /**
     * Header constructor
     *
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
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
        return sprintf('%s: %s', $this->name, $this->value);
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
        return $this->value;
    }
}
