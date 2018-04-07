<?php
namespace Neat\Http;

/**
 * HTTP Header Field
 */
class Header
{
    /**
     * Header field name
     *
     * @var string
     */
    public $name;

    /**
     * Header field value
     *
     * @var string
     */
    public $value;

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
    public function __toString()
    {
        return $this->line();
    }

    /**
     * Get header line
     *
     * @return string
     */
    public function line()
    {
        return sprintf('%s: %s', $this->name, $this->value);
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get header value
     *
     * @return string
     */
    public function value()
    {
        return $this->value;
    }
}
