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
        $this->setMethod($method);
        $this->setUrl($url);
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

    /**
     * Set method
     *
     * @param string $method
     */
    protected function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Set URL
     *
     * @param string $url
     */
    protected function setUrl($url)
    {
        $this->url = $url instanceof Url ? $url : new Url($url);
    }

    /**
     * Get new request with method
     *
     * @param string $method
     * @return Request
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->setMethod($method);

        return $new;
    }

    /**
     * Get new request with URL
     *
     * @param string|Url $url
     * @return Request
     */
    public function withUrl($url)
    {
        $new = clone $this;
        $new->setUrl($url);

        return $new;
    }
}
