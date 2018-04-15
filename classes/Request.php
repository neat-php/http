<?php
namespace Neat\Http;

/**
 * HTTP Request
 */
class Request extends Message
{
    /**
     * Method in uppercase
     *
     * @var string
     */
    protected $method;

    /**
     * URL
     *
     * @var Url
     */
    protected $url;

    /**
     * Request constructor.
     *
     * @param string     $method
     * @param string|Url $url
     * @param string     $body
     */
    public function __construct($method = 'GET', $url = null, $body = null)
    {
        $this->method = strtoupper($method);
        $this->url    = $url instanceof Url ? $url : new Url($url);
        $this->setBody($body);
    }

    /**
     * Get request as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->requestLine() . self::EOL . parent::__toString();
    }

    /**
     * Get request line
     *
     * @return string
     */
    public function requestLine()
    {
        $uri = $this->url->path();
        if ($this->url->query()) {
            $uri .= '?' . $this->url->query();
        }

        return sprintf('%s %s HTTP/%s', $this->method, $uri, $this->version);
    }

    /**
     * Get method
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Get URL
     *
     * @return Url
     */
    public function url()
    {
        return $this->url;
    }
}
