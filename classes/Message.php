<?php
namespace Neat\Http;

/**
 * HTTP Message
 */
class Message
{
    /**
     * HTTP header line ending
     */
    const EOL = "\r\n";

    /**
     * Body
     *
     * @var string
     */
    protected $body;

    /**
     * Headers indexed by lowercase name
     *
     * @var Header[]
     */
    protected $headers = [];

    /**
     * HTTP version
     *
     * @var string
     */
    protected $version = '1.1';

    /**
     * Get message as a string
     *
     * @return string
     */
    public function __toString()
    {
        $message = '';
        foreach ($this->headers as $header) {
            $message .= $header->line() . self::EOL;
        }
        $message .= self::EOL . $this->body;

        return $message;
    }

    /**
     * Get body
     *
     * @return string|null
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Get header value by name
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function header($name, $default = null)
    {
        $header = $this->headers[strtolower($name)] ?? null;

        return $header ? $header->value() : $default;
    }

    /**
     * Get header values
     *
     * @return array
     */
    public function headers()
    {
        $headers = [];
        foreach ($this->headers as $header) {
            $headers[$header->name()] = $header->value();
        }

        return $headers;
    }

    /**
     * Get HTTP version
     *
     * @return string
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * Set body
     *
     * @param string $body
     */
    protected function setBody($body = null)
    {
        if (is_null($body) || is_string($body)) {
            $this->body = $body;
        } elseif (is_array($body) || is_object($body)) {
            $this->body = json_encode($body);
            $this->setHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Get instance with body
     *
     * @param string $body
     * @return static
     */
    public function withBody($body = null)
    {
        $new = clone $this;
        $new->setBody($body);

        return $new;
    }

    /**
     * Set header
     *
     * @param string $name
     * @param string $value
     */
    protected function setHeader($name, $value)
    {
        $this->headers[strtolower($name)] = new Header($name, $value);
    }

    /**
     * Get instance with header
     *
     * @param string $name
     * @param string $value
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->setHeader($name, $value);

        return $new;
    }

    /**
     * Get instance without header
     *
     * @param string $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);

        return $new;
    }

    /**
     * Get instance with version
     *
     * @param string $version
     * @return static
     */
    public function withVersion(string $version)
    {
        $new = clone $this;
        $new->version = $version;

        return $new;
    }
}
