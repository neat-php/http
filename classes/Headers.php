<?php
namespace Neat\Http;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * HTTP Headers
 */
class Headers implements Countable, IteratorAggregate
{
    /**
     * Headers
     *
     * @var string[]
     */
    protected $headers = [];

    /**
     * Header names in lowercase equivalent
     *
     * @var string[]
     */
    protected $names = [];

    /**
     * Constructor
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Get headers as a string
     *
     * @return string
     */
    public function __toString()
    {
        return implode('', array_map(function ($line) {
            return $line . "\r\n";
        }, $this->lines()));
    }

    /**
     * Get header lines
     *
     * @return array
     */
    public function lines()
    {
        $lines = [];
        foreach ($this->headers as $name => $value) {
            $lines[] = sprintf('%s: %s', $name, $value);
        }

        return $lines;
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Has header value?
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        $name   = $this->names[strtolower($name)] ?? $name;
        $values = $this->headers[$name] ?? null;

        return !!$values;
    }

    /**
     * Get header value
     *
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function get(string $name, string $default = null)
    {
        $name = $this->names[strtolower($name)] ?? $name;

        return $this->headers[$name] ?? $default;
    }

    /**
     * Set header value
     *
     * @param string $name
     * @param string $value
     */
    public function set(string $name, string $value)
    {
        if (isset($this->names[strtolower($name)])) {
            unset($this->headers[$this->names[strtolower($name)]]);
        }

        $this->headers[$name] = $value;
        $this->names[strtolower($name)] = $name;
    }

    /**
     * Count headers
     *
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Get iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }

    /**
     * Capture the headers
     *
     * @param array $headers (optional)
     * @return static
     */
    public static function capture(array $headers = null)
    {
        return new static($headers ?? getallheaders());
    }
}
